package function;

# AUTHOR: Clemens Schwaighofer
# DATE CREATED: 2004/11/09
# DESCRIPTION: functions collection for Adidas scripts
# HISTORY:
# 2005/06/22 (cs) added header key check function
# 2005/02/10 (cs) added debug flag to print output, added two new functions to format a number into B, KB, etc
# 2005/01/13 (cs) fixed array problem with the clean up and int function

use strict;
use warnings;
use 5.000_000;
use POSIX qw(floor);
use File::Copy;
use Digest::SHA qw(sha1_hex);
use utf8;
#require Exporter;
#our @ISA = qw(Exporter);
#our @EXPORT = qw();

# depending on the options given to the program, it gets the correct settings
# to which db it should connect
sub get_db_user
{
	my ($target, $db) = @_;

	# the parts of the hash array (tab seperated)
	my @array_names = qw{db_name	db_port	db_user	db_pass	db_host	db_type	db_test	db_ssl};
	my %db_out = ();

	# based on the two parameters find the correct vars
	# each level can hold data, higher level data overrules lower data
	# eg $config::db{'test'}{'db_user'} overrules $config::db{'db_user'}
	for (my $i = 1; $i <= 3; $i ++) {
		foreach my $name (@array_names) {
			# depending on the level check the level of data
			if ($i == 1) {
				$db_out{$name} = $config::db{$name} if (defined($config::db{$name}));
			} elsif ($i == 2) {
				$db_out{$name} = $config::db{$target}{$name} if (defined($config::db{$target}{$name}));
			} elsif ($i == 3) {
				$db_out{$name} = $config::db{$target}{$db}{$name} if (defined($config::db{$target}{$db}{$name}));
			}
		} # for each db data var
	} # for each data level in the hash

	return (
		$db_out{'db_name'},
		$db_out{'db_port'},
		$db_out{'db_user'},
		$db_out{'db_pass'},
		$db_out{'db_host'},
		$db_out{'db_type'},
		$db_out{'db_test'},
		$db_out{'db_ssl'}
	);
}

# get the DSN string for the DB connect
sub get_db_dsn
{
	my (
		$db_name,
		$db_port,
		$db_user,
		$db_pass,
		$db_host,
		$db_type,
		$db_ssl
	) = @_;
	my $dsn = '';

	if ($db_type eq 'mysql' && $db_name && $db_host && $db_user) {
		$dsn = "DBI:mysql:database=".$db_name.";host=".$db_host.";port=".$db_port;
	} elsif ($db_type eq 'pgsql' && $db_name && $db_host && $db_user) {
		$dsn = "DBI:Pg:dbname=".$db_name.";host=".$db_host.";port=".$db_port.";sslmode=".$db_ssl;
	} else {
		# invalid db type
		$dsn = -1;
	}
	return $dsn;
}

sub strip_white_spaces
{
	my ($element) = @_;
	# get rid of spaces at the end and at the beginning of each bloack
	$element =~ s/^\s+//g;
	$element =~ s/\s+$//g;
	return $element;
}

sub prepare_hash_keys
{
	my($csv, $data, $csv_header) = @_;

	# unset value starts at 1000 and goes up ...
	my $unset_value = 1000;
	my %keys = ();

	# parse header
	if ($csv->parse($data)) {
		my @cols = $csv->fields();
		for (my $i = 0; $i < @cols; $i ++) {
			# remove all spaces before and afterward
			$cols[$i] = function::strip_white_spaces($cols[$i]);
			# write key - id number
			$keys{$cols[$i]} = $i;
			print $::DEBUG "\tPostion [".$i."]: ".$cols[$i]."\n" if ($::debug);
			print "\tPosition [".$i."]: ".$cols[$i]."\n" if ($::verbose > 1);
		}
	} else {
		die "ERROR[".$csv->error_diag()."]: ".$csv->error_input()."\n";
	}
	# add empty values
	foreach my $csv_header_value (@$csv_header) {
		if (!defined($keys{$csv_header_value})) {
			$keys{$csv_header_value} = $unset_value;
			$unset_value ++;
			print $::DEBUG "\tKey [$csv_header_value] gets position [".$keys{$csv_header_value}."]\n" if ($::debug);
			print "\tKey [$csv_header_value] gets position [".$keys{$csv_header_value}."]\n" if ($::verbose > 1);
		}
	}

	return %keys;
}

sub error_check_keys
{
	my($csv_header, $keys) = @_;

	if ((keys %$keys) != @$csv_header) {
		print $::ERR "TOTAL WRONG COUNT: CSV header ".(keys %$keys)." vs Needed headers ".@$csv_header.": perhaps your input file is not fitting this?\n";
		print "TOTAL WRONG COUNT: CSV header ".(keys %$keys)." vs Needed headers ".@$csv_header.": perhaps your input file is not fitting this?\n";

		# if there are more keys in CSV file, then in the header defined in here
		if ((keys %$keys) > @$csv_header) {
			print $::ERR "Listing Perl Header missing\n";
			print "Listing Perl Header missing\n";
			foreach my $key (keys %$keys) {
				print $::ERR "Missing in perl Header list: $key\n" if (!grep {$_ eq $key} @$csv_header);
				print "Missing in perl Header list: $key\n" if (!grep {$_ eq $key} @$csv_header);
			}
			# if more keys are in the header defined than in the csv file
		} else {
			print $::ERR "Listing CSV Header missing\n";
			print "Listing CSV Header missing\n";
			for (my $i = 0; $i < @$csv_header; $i ++) {
				print $::ERR "Missing in CSV file: ".$$csv_header[$i]."\n" if (!defined($$keys{$$csv_header[$i]}));
				print "Missing in CSV file: ".$$csv_header[$i]."\n" if (!defined($$keys{$$csv_header[$i]}));
			}
		}
		return 0;
	}
	return 1;
}

sub clean_up_row
{
	my ($row) = @_;

	for (my $i = 0; $i < @$row; $i++) {
		# get rid of spaces at the end and at the beginning of each bloack
		$$row[$i] =~ s/^\s+//g;
		$$row[$i] =~ s/\s+$//g;
		# convert all half width Katakan to Full width Katakana
		$$row[$i] = Unicode::Japanese->new($$row[$i])->h2zKana->get;
		# need to decode the converted string, somehow Unicode::Japanese does not return proper utf8 if use utf8 is on
		utf8::decode($$row[$i]);
	}

	return @$row;
}

sub set_int_fields
{
	my ($row, $keys, $int_fields) = @_;

	# check ALL smallint/int/etc rows to be set to a number
	for (my $i = 0; $i < @$int_fields; $i++) {
		print "\t\tCheck ".$$int_fields[$i]." {".$$keys{$$int_fields[$i]}."} ... " if ($::verbose > 1);
		if (!$$row[$$keys{$$int_fields[$i]}]) {
			$$row[$$keys{$$int_fields[$i]}] = 0;
		}
		# if its filled, but not a digit, set to 1
		if ($$row[$$keys{$$int_fields[$i]}] =~ /\D/) {
			$$row[$$keys{$$int_fields[$i]}] = 1;
		}
		print "[".$$row[$$keys{$$int_fields[$i]}]."] [DONE]\n" if ($::verbose > 1);
	}
	return @$row;
}

# formats a number with dots and ,
sub format_number
{
	my ($number) = @_;
	# dummy, does nothing now
	# should put . or , every 3 digits later
	return $number;
}

# converts bytes to human readable format
sub convert_number
{
	my ($number) = @_;
	my $pos; # the original position in the labels array
	# divied number until its division would be < 1024. count that position for label usage
	for ($pos = 0; $number > 1024; $pos ++) {
		$number = $number / 1024;
	}
	# before we return it, we format it [rounded to 2 digits, if has decimals, else just int]
	# we add the right label to it and return
	return sprintf(!$pos ? '%d' : '%.2f', $number)." ".qw(B KB MB GB TB PB EB)[$pos];
}

# make time from seconds string
sub convert_time
{
	my ($timestamp, $show_micro) = @_;
	my $ms = '';
	# cut of the ms, but first round them up to four
	$timestamp = sprintf("%.4f", $timestamp);
	# print "T: ".$timestamp."\n";
	($timestamp, $ms) = split(/\./, $timestamp);
	my @timegroups = ("86400", "3600", "60", "1");
	my @output = ();
	for (my $i = 0; $i < @timegroups; $i ++) {
		push(@output, floor($timestamp / $timegroups[$i]));
		$timestamp = $timestamp % $timegroups[$i];
	}
	# output has days|hours|min|sec
	return (($output[0]) ? $output[0]."d " : "").
		(($output[1] || $output[0]) ? $output[1]."h " : "").
		(($output[2] ||$output[1] || $output[0]) ? $output[2]."m " : "").
		$output[3]."s".
		(($show_micro) ? " ".((!$ms) ? 0 : $ms)."ms" : "");
}

# get a timestamp and create a proper formated date/time field
sub create_time
{
	my ($timestamp, $show_micro) = @_;
	my $ms = '';
	$timestamp = 0 if (!$timestamp);
	# round ms to 4 numbers
	$timestamp = sprintf("%.4f", $timestamp);
	($timestamp, $ms) = split(/\./, $timestamp);
	# array for time
	my ($sec, $min, $hour, $day, $month, $year, $wday, $yday, $isdst) = localtime($timestamp);
	# year, month fix
	$year += 1900;
	$month += 1;
	# string for return
	return $year."-".
		($month < 10 ? '0'.$month : $month)."-".
		($day < 10 ? '0'.$day : $day)." ".
		($hour < 10 ? '0'.$hour : $hour).":".
		($min < 10 ? '0'.$min : $min).":".
		($sec < 10 ? '0'.$sec : $sec).
		(($ms && $show_micro) ? ".".$ms : "");
}

# create YYYYMMDD data
sub create_date
{
	my ($timestamp, $split_string) = @_;
	my $split = $split_string ? $split_string : '';
	$timestamp = time() if (!$timestamp);
	# array for time
	my ($sec, $min, $hour, $day, $month, $year, $wday, $yday, $isdst) = localtime($timestamp);
	# year, month fix
	$year += 1900;
	$month += 1;
	# string for return
	return $year.$split.
		($month < 10 ? '0'.$month : $month).$split.
		($day < 10 ? '0'.$day : $day);
}

# create YYYYMMDD_HHMMSS data
sub create_datetime
{
	my ($timestamp, $split_string) = @_;
	my $split = $split_string ? $split_string : '';
	$timestamp = time() if (!$timestamp);
	# array for time
	my ($sec, $min, $hour, $day, $month, $year, $wday, $yday, $isdst) = localtime($timestamp);
	# year, month fix
	$year += 1900;
	$month += 1;
	# string for return
	return $year.$split.
		($month < 10 ? '0'.$month : $month).$split.
		($day < 10 ? '0'.$day : $day).'_'.
		($hour < 10 ? '0'.$hour : $hour).$split.
		($min < 10 ? '0'.$min : $min).$split.
		($sec < 10 ? '0'.$sec : $sec);
}

sub left_fill
{
	my($number, $size, $char) = @_;
	return sprintf($char x ($size - length($number)).$number);
}

# wrapper to flip the crc32 hex string, so it is like buggy php one (php <= 5.2.6)
sub crc32b_fix
{
	my ($crc) = @_;
	# left pad with 0 to 8 chars
	$crc = ('0' x (8 - length($crc))).$crc;
	# flip two chars (byte hex)
	$crc =~ s/^([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})([a-z0-9]{2})$/$4$3$2$1/;
	return $crc;
}

# short sha1 (9 char) function
sub sha1_short
{
	my ($string) = @_;
	return substr(sha1_hex($string), 0, 9);
}

# DEBUG helpers for dumping data
# from: http://www.perlmonks.org/?node_id=390153
# alternative use Dump::Dumper and print Dump(VAR);
sub dump_data
{
	my ($level, $base, $data) = @_;
	my $nextlevel = $level + 1;
	if (ref($data) eq 'ARRAY') {
		foreach my $k (0 .. $#{$data}) {
			my $baseval = $base.'['.$k.']';
			dump_it($nextlevel, $baseval, $data->[$k]);
		}
	} elsif (ref($data) eq 'HASH') {
		foreach my $k (sort(keys(%{$data}))) {
			my $baseval = $base.'{'.$k.'}';
			dump_it($nextlevel, $baseval, $data->{$k});
		}
	} elsif (ref($data) eq 'SCALAR') {
		my $baseval = $base;
		dump_it($nextlevel, $baseval, ${$data});
	}
}

sub dump_it
{
	my ($nextlevel, $baseval, $datum) = @_;
	my $reftype = ref($datum);
	if ($reftype eq 'HASH') {
		dump_data($nextlevel, $baseval, \%{$datum});
	} elsif ($reftype eq 'ARRAY') {
		dump_data($nextlevel, $baseval, \@{$datum});
	} else {
		process_data($nextlevel, $baseval, $datum);
	}
}

sub process_data
{
	my ($nextlevel, $baseval, $datum) = @_;
	my $indentation = '  ' x $nextlevel;
	print $indentation, $baseval, ' = ', $datum, "\n";
}

# METHOD: lock_run
# PARAMS: file (plus path) to lock to
#         the current running pid (if not given will be set in script)
#         the current name of the script (auto set if not given)
#         optional write encoding (set to utf8 if not given)
# RETURN: nothing
# DESC:   checks if this script is already running based on the lock file, if if yes will abort
#         if file is there but pid not find it automatically cleans up the stale lock file
sub lock_run
{
	my ($file, $run_pid, $name, $encoding) = @_;
	# if no encoding, set utf8
	$encoding = 'utf8' if (!$encoding);
	# set the run pid if no pid is given
	$run_pid = $$ if (!$run_pid);
	# set the script base name
	$name = File::Basename::fileparse($0) if (!$name);
	# if lock file exists
	if (-f $file) {
		my $exists = 0;
		my $pid = `cat $file`;
		chomp($pid);
		# printDebug("Lock file found for $pid", 1);
		# check if process excists with this pid
		# better todo A for ALL processes
		# ps axu OR short ps a
		open(PS, 'ps axu|') || die("$!");
		while (<PS>) {
			# search for pid and run file name
			if ($_ =~ /\ $pid\ / && $_ =~ /$name/) {
				$exists = 1;
			}
			last if ($exists);
		}
		close(PS);
		if (!$exists) {
			# printDebug("Lock file cleaned up for $pid", 1);
			unlink($file);
		} else {
			die("Script is already running with PID $pid\n");
		}
	}
	# write current PID into lock file
	open(FP, '>:encoding('.$encoding.')', $file) || die ("Cannot open run lock file '$file' for writing\n");
	print FP $run_pid;
	close(FP);
}

# METHOD: printDebug
# PARAMS: message, verbose level
# RETURN: nothing
# DESC:   depeding on the verbose and debug settings it will print out message and or write it to a debug file
sub printDebug
{
	my($msg, $vrb, $dbg) = @_;
	# print debug only if debug is on and debug file is available
	print $::DEBUG '['.create_time(time(), 1).'] '.$msg."\n" if ($::debug && $::DEBUG);
	# print to log if log is accessable and the verbose flag matches, or for debug flag if debug statement is set and not log only, or if log only, if not debug statement
	print $::LOG $msg."\n" if (($::verbose >= $vrb || (!$::log_only && $dbg && $::debug) || ($::log_only && !$dbg)) && $::LOG);
	# print to screen if verbose matches, but it is not a log only, or if it is debug statement and debug flag is set
	print $msg."\n" if (($::verbose >= $vrb && !$::log_only) || ($dbg && $::debug));
}

# METHOD: waitAbort
# PARAMS: time in seconds, if not provided set to 5
# RETURN: nothing
# DESC:   simple prints out a char while waiting for an abort command
sub waitAbort
{
	my($sleep) = @_;
	$sleep = 5 if ($sleep !~ /\d/);
	print "Waiting $sleep seconds (Press CTRL + C to abort)\n";
	for (my $i = 1; $i <= $sleep; $i ++) {
		print ".";
		sleep 1;
	}
	print "\n\n";
}

# METHOD: copyToTemporary
# PARAMS: file to copy, and target file name
# RETURN: the target file name
# DESC  : sets the source to read only and makes a copy, the copy is also set to read only
sub copyToTemporary
{
	my ($source, $target) = @_;
	# get the current rights
	my $current_chmod = (stat $source)[2];
	# set source file ARGV to read only
	# we skip that, the source might be NOT from the same user as the script read, just copy the file and set the target read only
	chmod(0444, $source);
	# create tmp backup file from which we read, data gets removed at the end of an run, or during an abort call
	copy($source, $target) || die("Copy failed: $!\n");
	# set read rights to r only for the copied file
	chmod(0444, $target);
	# set old access rights for ARGV file
	chmod($current_chmod, $source);
	# return target file name
	return $target;
}

# METHOD: uniq
# PARAMS: @array
# RETURN: array with only unique entries
# DESC  : used in uniq(@array) to get only unique data back
sub uniq
{
	my %seen;
	grep !$seen{$_}++, @_;
}

# METHOD: clean_test
# PARAMS: array of data
# RETURN: cleaned up array of data
# DESC  : sets all undefs to '' for debug output
sub clean_test
{
	my (@data) = @_;
	# map check for defined, if not, return ''
	return map { defined($_) ? $_ : '' } @data;
}

# METHOD: clean_test_string
# PARAMS: string to be checked
# RETURN: data or empty for output
# DESC  : sets all input data to '' if it is undefined
sub clean_test_string
{
	my ($data) = @_;
	return defined($data) ? $data : '';
}

1;
