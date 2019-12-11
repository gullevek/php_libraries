package Progress;

# AUTHOR: Clemens Schwaighofer
# DATE CREATED: 2009/6/16
# DESCRIPTION: progress percent class

# METHODS
# * init
# my $prg = Progress->new();
#   will init a new progress class in the var $prg
#   the following parameters can be set directly during a new call
#   - verbose (1/0)
#   - precision (-1~10)
#   - wide_time (0/1)
#   - microtime (0/1)
#   setting is done via
# my $prg = Progress->new(verbose => 1, microtime = 1);
# * setting methods
# verbose($level int)
#   $level has to be int, if not set there is no output show, at least 1 has to be given to see visible output
# precision($decimals int)
#   $decimals has to be int, if set to -1 then the steps are done in 10 increase, else it sets how many decimals are visible, 0 for no decimals
# wide_time(0/1 int)
#   sets the flag for wide time, if set to 1 the estimated time to end and time run is left prefixed with 15 chars
# microtime(0/1 int)
#   sets the flag to always show microtime (1) or only if the previous time was the same (0)
# reset()
#   resets all the internal vars for another new run
# SetStartTime(optional timestamp)
#   sets the start times for this progress run, the overall start/end time is set, and the time used for the actual progress
#   in case there is some processing done before the run starts, it is highly recommended to call SetETAStartTime before the actual processing starts
#   if no timestamp is given, internal timestamp is used (this is recommended)
# SetETAStartTime(optional timestamp)
#   only sets the start/end time for the actual "estimated time" calculation. It is recommended to call this right before the processing loop starts
#   eg if there is a big query running that takes a lot of time, this method should be called before the reading loop
#   as with SetStartTime a timestamp can be given, if not then the internal timestamp is used (this is recommended)
# SetEndTime(optional timestamp)
#   sets the end time for the overall processing. This should be called at the very end of the script before any final stat data is printed
# linecount($lines int)
#   sets the maximum lines that will be processed, used for percentage calculation. If non int is given, will set to 1. This will be only set once, to
#   reset used reset() method.
#   Either this or filesize NEED to be set
# filesize($bytes int)
#   filesize in bytes, if non valid data is given, then it is set to 1.
#   filesize() and linecount() can both be set, but at least one of them has to be set.
#   if filesize is set a byte data output is added, if only linecount is given, only the linecount output will be given (no bytes per second, etc)
# ShowPosition(optional current byte position int)
#   this is the main processing and has to be called at the end of the loop where the data is processed. If no bytes are given the internal counter (linecount)
#   is used.
#   for bytes it is recommended to use IO::File and $FH->tell to pass on the bytes
#
# VARIABLES
# * internal set
#   change: flagged 1 if output is given or would be given. can be used for any post processing after the ShowPosition is called
#   precision_ten_step: flagged 1 if the precision was set to -1
#   start: overall start time
#   end: overall end time
#   count: count of processed lines
#   [TODO: describe the others too, at the moment only below in %fields]

use strict;
use warnings;
use utf8;

BEGIN {
	use POSIX;
	use Carp;
	use Time::HiRes qw(time);
	use File::Basename;
	use Number::Format qw(format_number);
	use vars qw($AUTOLOAD);
	push(@INC, File::Basename::dirname($0).'/');
}

# important includes
use functions;

# variable declarationf or access
# * can be set
# = only for read
# unmarked are internal only, but can be read if they are needed in further processing in the script
my %fields = (
	linecount => 0, # * max lines in input
	filesize => 0, # * max file size
	precision => 1, # * comma after percent
	wide_time => 0, # * if flagged 1, then the wide 15 char left bound format is used
	verbose => 0, # * verbose status from outside
	microtime => 0, # * microtime output for last run time (1 for enable, 0 for auto, -1 for disable)
	change => 0, # = flag if output was given
	start => undef, # = global start for the full script running time
	start_run => undef, # = for the eta time, can be set after a query or long read in, to not create a wrong ETA time
	start_time => undef, # loop start
	end => undef, # = global end
	end_time => undef, # loop end
	count_size => undef, # = filesize current
	count => 0, # = position current
	current_count => 0, # last count (position)
	lines_processed => 0, # lines processed in the last run
	last_group => 0, # time in seconds for the last group run (until percent change)
	lines_in_last_group => 0, # float value, lines processed per second to the last group run
	lines_in_global => 0, # float values, lines processed per second to complete run
	bytes_in_last_group => 0, # flaot value, bytes processes per second in the last group run
	bytes_in_global => 0, # float value, bytes processed per second to complete run
	size_in_last_group => 0, # bytes processed in last run (in bytes)
	current_size => 0, # current file position (size)
	last_percent => 0, # last percent position
	precision_ten_step => 0, # if we have normal % or in steps of 10
	percent_print => 5, # the default size, this is precision + 4
	percent_precision => 1, # this is 1 if it is 1 or 0 for precision, or precision size
	eta => undef, # estimated time to finish
	full_time_needed => undef, # run time since start
	lg_microtime => 0 # last group microtime, this is auto set during process.
);

# class init
sub new
{
	my $proto = shift;
	my $class = ref($proto) || $proto;
	my %data = @_;
	my $self = {
		_permitted => \%fields,
		%fields,
	};
	# vars to init
	bless ($self, $class);
	if ($data{'verbose'} && $data{'verbose'} =~ /^\d{1}$/) {
		$self->{verbose} = $data{'verbose'};
	}
	if (exists($data{'precision'}) && (($data{'precision'} || $data{'precision'} == 0) && $data{'precision'} =~ /^\-?\d{1,2}$/)) {
		$self->precision($data{'precision'});
	}
	if ($data{'microtime'} && $data{'microtime'} =~ /^(0|1)$/) {
		$self->microtime($data{'microtime'});
	}
	if ($data{'wide_time'} && $data{'wide_time'} =~ /^(0|1)$/) {
		$self->wide_time($data{'wide_time'});
	}
	return $self;
}

# auto load for vars
sub AUTOLOAD
{
	my $self = shift;
	my $type = ref($self) || croak "$self is not an object";
	my $name = $AUTOLOAD;
	$name =~ s/.*://;

	unless (exists $self->{_permitted}->{$name}) {
		croak "Can't access '$name' field in class $type";
	}

	if (@_) {
		return $self->{$name} = shift;
	} else {
		return $self->{$name};
	}
}

# destructor
sub DESTROY
{
	# do nothing, there is nothing to close or finish
}

# SUB: reset
# PARAMS: none
# DESC: resets all the current counters only and current start times
sub reset
{
	my $self = shift;
	# reset what always gets reset
	$self->{count} = 0;
	$self->{count_size} = undef;
	$self->{current_count} = 0;
	$self->{linecount} = 0;
	$self->{lines_processed} = 0;
	$self->{last_group} = 0;
	$self->{lines_in_last_group} = 0;
	$self->{lines_in_global} = 0;
	$self->{bytes_in_last_group} = 0;
	$self->{bytes_in_global} = 0;
	$self->{size_in_last_group} = 0;
	$self->{filesize} = 0;
	$self->{current_size} = 0;
	$self->{last_percent} = 0;
	$self->{eta} = 0;
	$self->{full_time_needed} = 0;
	$self->{start_run} = undef;
	$self->{start_time} = undef;
	$self->{end_time} = undef;
}

# SUB: microtime
# PARAMS: 1/0
# DESC: flag to set microtime on or off in the time output
#       if not 1 or 0, set to 0
sub microtime
{
	my $self = shift;
	my $microtime;
	if (@_) {
		$microtime = shift;
		if ($microtime == 1 || $microtime == 0) {
			$self->{microtime} = $microtime;
		} else {
			$self->{microtime} = 0;
		}
	}
	return $self->{microtime};
}


# SUB: wide_time
# PARAMS: 1/0
# DESC: flag to set wide_time (15 char spacer).
#       if not 1 or 0, set to 0
sub wide_time
{
	my $self = shift;
	my $wide;
	if (@_) {
		$wide = shift;
		if ($wide == 1 || $wide == 0) {
			$self->{wide_time} = $wide;
		} else {
			$self->{wide_time} = 0;
		}
	}
	return $self->{wide_time};
}

# SUB: precision
# PARAMS: precision in int
# DESC: sets the output percent precision calculation and printf width
#       if negative, to ten step, if bigger 10, set to one
sub precision
{
	my $self = shift;
	my $comma;
	if (@_) {
		$comma = shift;
		$comma = 0 if ($comma !~ /^\-?\d{1,}$/);
		if ($comma < 0) {
			# -2 is 5 step
			# -1 is 10 step
			if ($comma < -1) {
				$self->{precision_ten_step} = 5;
			} else {
				$self->{precision_ten_step} = 10;
			}
			$self->{precision} = 0; # no comma
			$self->{percent_precision} = 0; # no print precision
			$self->{percent_print} = 3; # max 3 length
		} else {
			$self->{precision} = $comma < 0 || $comma > 10 ? 10 : $comma;
			$self->{percent_precision} = $comma < 0 || $comma > 10 ? 10 : $comma;
			$self->{percent_print} = ($comma == 0 ? 3 : 4) + $self->{percent_precision};
		}
	}
	return $self->{precision};
}

# SUB: linecount
# PARAMS: max number of lines to be processed
# DESC: sets the max number for lines for the percent calculation, if negative or not number, set to 1
#       can only be set ONCE
sub linecount
{
	my $self = shift;
	my $linecount;
	if (!$self->{linecount}) {
		if (@_) {
			$linecount = shift;
			$self->{linecount} = $linecount;
			$self->{linecount} = 1 if ($linecount < 0 || $linecount !~ /\d+/)
		}
	}
	return $self->{linecount};
}

# SUB: filesize
# PARAMS: max filesize for the to processed data
# DESC: sets the max filesize for the to processed data, if negative or not number, set to 1
#       input data has to be in bytes without any suffix (no b, kb, etc)
#       can only be set ONCE
sub filesize
{
	my $self = shift;
	my $filesize;
	if (!$self->{filesize}) {
		if (@_) {
			$filesize = shift;
			$self->{filesize} = $filesize;
			$self->{filesize} = 1 if ($filesize < 0 || $filesize !~ /\d+/)
		}
	}
	return $self->{filesize};
}

# SUB: SetStartTime
# PARAMS: time, or nothing
# DESC: sets all the start times
sub SetStartTime
{
	my $self = shift;
	if (@_) {
		$self->{start} = shift;
	} else {
		$self->{start} = time();
	}
	$self->{start_time} = $self->{start};
	$self->{start_run} = $self->{start};
}

# SUB: SetETAStartTime
# PARAMS: time, or nothing
# DESC: sets the loop & run time, for correct ETA callculation
sub SetETAStartTime
{
	my $self = shift;
	if (@_) {
		$self->{start_time} = shift;
	} else {
		$self->{start_time} = time();
	}
	$self->{start_run} = $self->{start_time};
}

# SUB: SetEndTime
# PARAMS: time, or nothing
# DESC: sets the end time for running time calculation
sub SetEndTime
{
	my $self = shift;
	if (@_) {
		$self->{end} = shift;
	} else {
		$self->{end} = time();
	}
}

# SUB: ShowPosition
# PARAMS: optiona; file position (via file pointer)
# RETURN: string for percent position output
# DESC: calculates the current percent position based on the passed parameter, if no parameter uses intneral counter
sub ShowPosition
{
	my $self = shift;
	# set local vars
	my $percent; # current percent
	my $full_time_needed; # complete process time
	my $full_time_per_line; # time per line
	my $eta; # estimated end time
	my $string = ''; # percent string that gets output
	my $show_filesize = 1;
	# microtime flags
	my $eta_microtime = 0;
	my $ftn_microtime = 0;
	my $lg_microtime = 0;
	# percent precision calc
	my $_p_spf = "%.".$self->{precision}."f";
	# output format for percent
	my $_pr_p_spf = "%".$self->{percent_print}.".".$self->{percent_precision}."f";
	# set the linecount precision based on the final linecount, if not, leave it empty
	my $_pr_lc = "%s";
	$_pr_lc = "%".length(format_number($self->{linecount}))."s" if ($self->{linecount});
	# time format, if flag is set, the wide format is used
	my $_pr_tf = "%s";
	$_pr_tf = "%-15s" if ($self->{'wide_time'});
	# do the smae for file size
	# my $_pr_fs = "%s";
	# $_pr_fs = "%".length(function::convert_number($self->{filesize}))."s" if ($self->{filesize});

	# increase position by one
	$self->{count} ++;
	# see if we get anything from IO tell
	if (@_) {
		$self->{file_pos} = shift;
	} else {
		# we did not, so we set internal value
		$self->{file_pos} = $self->{count};
		# we also check if the filesize was set now
		if (!$self->{filesize}) {
			$self->{filesize} = $self->{linecount};
		}
		# set ignore filesize output (no data)
		$show_filesize = 0;
	}
	# set the count size based on the file pos, is only used if we have filesize
	$self->{count_size} = $self->{file_pos};

	# do normal or down to 10 (0, 10, ...) %
	if ($self->{precision_ten_step}) {
		# calc 0 comma precision, so just do a floor
		my $_percent = sprintf("%d", ($self->{file_pos} / $self->{filesize}) * 100);
		# mod that to 10
		my $mod = $_percent % $self->{precision_ten_step};
		# either write this one, or write the previous, old one
		$percent = $mod == 0 ? $_percent : $self->last_percent;
		# print "P: $percent, Last: ".$self->last_percent.", Mod: ".$mod.", Calc: ".$_percent."\n";
	} else {
		$percent = sprintf($_p_spf, ($self->{file_pos} / $self->{filesize}) * 100);
	}
	# print "POS: ".$self->{file_pos}.", PERCENT: $percent / ".$self->last_percent."\n";
	if ($percent != $self->last_percent) {
		$self->{end_time} = time();
		# for from the beginning
		$full_time_needed = $self->{end_time} - $self->{start_run}; # how long from the start;
		$self->{last_group} = $self->{end_time} - $self->{start_time};
		$self->{lines_processed} = $self->{count} - $self->{current_count};
		# lines in last group
		$self->{lines_in_last_group} = $self->{'last_group'} ? ($self->{lines_processed} / $self->{last_group}) : 0;
		# lines in global
		$self->{lines_in_global} = $full_time_needed ? ($self->{'count'} / $full_time_needed) : 0;
		# if we have linecount
		if (!$self->{linecount}) {
			$full_time_per_line = (($full_time_needed) ? $full_time_needed : 1) / $self->{count_size}; # how long for all
			$eta = $full_time_per_line * ($self->{filesize} - $self->{count_size}); # estimate for the rest
		} else {
			$full_time_per_line = (($full_time_needed) ? $full_time_needed : 1) / $self->{count}; # how long for all
			$eta = $full_time_per_line * ($self->{linecount} - $self->{count}); # estimate for the rest
		}

		# just in case ...
		$eta = '0' if ($eta < 0);
		# check if to show microtime
		# ON: if microtime is flagged as one
		$eta_microtime = $ftn_microtime = $lg_microtime = 1 if ($self->{microtime} == 1);
		# AUTO: foir microtime
		if ($self->{microtime} == 0) {
			$eta_microtime = 1 if ($eta > 0 && $eta < 1);
			$ftn_microtime = 1 if ($full_time_needed > 0 && $full_time_needed < 1);
			# pre check last group: if pre comma part is same add microtime anyway
			$lg_microtime = 1 if ($self->{last_group} > 0 && $self->{last_group} < 1);
		}
		# print out
		if ($show_filesize) {
			# last group size
			$self->{size_in_last_group} = $self->{count_size} - $self->{current_size};
			# calc kb/s if there is any filesize data
			# last group
			$self->{bytes_in_last_group} = $self->{'last_group'} ? ($self->{size_in_last_group} / $self->{last_group}) : 0;
			# global
			$self->{bytes_in_global} = $full_time_needed ? ($self->{count_size} / $full_time_needed) : 0;
			# only used if we run with file size for the next check
			$self->{current_size} = $self->{count_size};

			$string = sprintf(
				"Processed ".$_pr_p_spf."%% [%s / %s] | ".$_pr_lc." / ".$_pr_lc." Lines | ETA: ".$_pr_tf." / TR: ".$_pr_tf." / LR: %s lines (%s) in %s, %s (%s) lines/s, %s (%s) b/s\n",
				$percent,
				function::convert_number($self->{count_size}),
				function::convert_number($self->{filesize}),
				format_number($self->{count}),
				format_number($self->{linecount}),
				function::convert_time($eta, $eta_microtime),
				function::convert_time($full_time_needed, $ftn_microtime),
				format_number($self->{lines_processed}),
				function::convert_number($self->{size_in_last_group}),
				function::convert_time($self->{last_group}, $lg_microtime),
				format_number($self->{lines_in_global}, 2, 1),
				format_number($self->{lines_in_last_group}, 2, 1),
				function::convert_number($self->{bytes_in_global}),
				function::convert_number($self->{bytes_in_last_group})
			) if ($self->{verbose} >= 1);
		} else {
			$string = sprintf(
				"Processed ".$_pr_p_spf."%% | ".$_pr_lc." / ".$_pr_lc." Lines | ETA: ".$_pr_tf." / TR: ".$_pr_tf." / LR: %s lines in %s, %s (%s) lines/s\n",
				$percent,
				format_number($self->{count}),
				format_number($self->{linecount}),
				function::convert_time($eta, $eta_microtime),
				function::convert_time($full_time_needed, $ftn_microtime),
				format_number($self->{lines_processed}),
				function::convert_time($self->{last_group}, $lg_microtime),
				format_number($self->{lines_in_global}, 2, 1),
				format_number($self->{lines_in_last_group}, 2, 1)
			) if ($self->{verbose} >= 1);
		}
		# write back vars
		$self->{last_percent} = $percent;
		$self->{eta} = $eta;
		$self->{full_time_needed} = $full_time_needed;
		$self->{lg_microtime} = $lg_microtime;
		# for the next run, check data
		$self->{start_time} = time();
		$self->{current_count} = $self->{count};
		# trigger if this is a change
		$self->{change} = 1;
	} else {
		# trigger if this is a change
		$self->{change} = 0;
	}
	return $string;
}

1;
