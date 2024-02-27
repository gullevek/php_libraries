/* only used for datepickr */

// METHOD: initDatepickr
// PARAMS: initial date ID (#)
// RETURN: true on ok, false on failure
// DESC  : inits date pickr which translations for dates (week/month)
function initDatepickr(init_date)
{
	if ($(init_date)) {
		datepickr('#' + init_date); // we need to add this so we have it initialized before we can actually change the definitions
		// dates in japanese
		datepickr.prototype.l10n.months.shorthand = [__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec')];
		datepickr.prototype.l10n.months.longhand = [__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')];
		datepickr.prototype.l10n.weekdays.shorthand = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
		datepickr.prototype.l10n.weekdays.longhand = [__('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday'), __('Sunday')];
		return true;
	} else {
		return false;
	}
}
