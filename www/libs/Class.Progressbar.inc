<?php
/*
*	Class ProgressBar
*
*	Author:		Gerd Weitenberg (hahnebuechen@web.de)
*	Date:		2005.03.09
*
*	Update:		Clemens Schwaighofer
*	Date:		2012.9.5 [stacked output]
*	Date:		2013.2.21 [proper class formatting]
*
*/

class ProgressBar
{
	// private vars
	
	public $code;	// unique code
	public $status = 'new';	// current status (new,show,hide)
	public $step = 0;	// current step
	public $position = array(); // current bar position

	public $clear_buffer_size = 1; // we need to send this before the lfush to get browser output
	public $clear_buffer_size_init = 1024*1024; // if I don't send that junk, it won't send anything
	
	// public vars
	
	public $min = 0;	// minimal steps
	public $max = 100;	// maximal steps
	
	public $left = 5;	// bar position from left
	public $top = 5;	// bar position from top
	public $width = 300;	// bar width
	public $height = 25;	// bar height
	public $pedding = 0;	// bar pedding
	public $color = '#0033ff';	// bar color
	public $bgr_color = '#c0c0c0';	// bar background color
	public $bgr_color_master = '#ffffff';	// master div background color
	public $border = 1;			// bar border width
	public $brd_color = '#000000';	// bar border color
	
	public $direction = 'right';	// direction of motion (right,left,up,down)
	
	public $frame = array('show' => false);	// ProgressBar Frame
	/*	'show' => false,	# frame show (true/false)
		'left' => 200,	# frame position from left
		'top' => 100,	# frame position from top
		'width' => 300,	# frame width
		'height' => 75,	# frame height
		'color' => '#c0c0c0',	# frame color
		'border' => 2,		# frame border
		'brd_color' => '#dfdfdf #404040 #404040 #dfdfdf'	# frame border color
	*/
	
	public $label = array();	// ProgressBar Labels
	/*	'name' => array(	# label name
			'type' => 'text',	# label type (text,button,step,percent,crossbar)
			'value' => 'Please wait ...',	# label value
			'left' => 10,	# label position from left
			'top' => 20,	# label position from top
			'width' => 0,	# label width
			'height' => 0,	# label height
			'align' => 'left',	# label align
			'font-size' => 11,	# label font size
			'font-family' => 'Verdana, Tahoma, Arial',	# label font family
			'font-weight' => '',	#	label font weight
			'color' => '#000000',	#	label font color
			'bgr_color' => ''	# label background color
		)
	*/
	
	// constructor
	public function __construct($width = 0, $height = 0)
	{
		$this->code = substr(md5(microtime()), 0, 6);
		if ($width > 0)
			$this->width = $width;
		if ($height > 0)
			$this->height = $height;
		// needs to be called twice or I do not get any output
		$this->_flushCache($this->clear_buffer_size_init);
		$this->_flushCache($this->clear_buffer_size_init);
	}
	
	// private functions
	
	private function _flushCache($clear_buffer_size = 0)
	{
		if (!$clear_buffer_size)
			$clear_buffer_size = $this->clear_buffer_size;
		echo str_repeat(' ', $clear_buffer_size);
		ob_flush();
		flush();
	}

	private function _calculatePercent($step)
	{
		// avoid divison through 0
		if ($this->max - $this->min == 0)
			$this->max ++;
		$percent = round(($step - $this->min) / ($this->max - $this->min) * 100);
		if ($percent > 100)
			$percent = 100;
		return $percent;
	}
	
	private function _calculatePosition($step)
	{
		switch ($this->direction)
		{
			case 'right':
			case 'left':
				$bar = $this->width;
				break;
			case 'down':
			case 'up':
				$bar = $this->height;
				break;
		}
		// avoid divison through 0
		if ($this->max - $this->min == 0)
			$this->max ++;
		$pixel = round(($step - $this->min) * ($bar - ($this->pedding * 2)) / ($this->max - $this->min));
		if ($step <= $this->min)
			$pixel = 0;
		if ($step >= $this->max)
			$pixel = $bar - ($this->pedding * 2);
		
		switch ($this->direction)
		{
			case 'right':
				$position['left'] = $this->pedding;
				$position['top'] = $this->pedding;
				$position['width'] = $pixel;
				$position['height'] = $this->height - ($this->pedding * 2);
				break;
			case 'left':
				$position['left'] = $this->width - $this->pedding - $pixel;
				$position['top'] = $this->pedding;
				$position['width'] = $pixel;
				$position['height'] = $this->height - ($this->pedding * 2);
				break;
			case 'down':
				$position['left'] = $this->pedding;
				$position['top'] = $this->pedding;
				$position['width'] = $this->width - ($this->pedding * 2);
				$position['height'] = $pixel;
				break;
			case 'up':
				$position['left'] = $this->pedding;
				$position['top'] = $this->height - $this->pedding - $pixel;
				$position['width'] = $this->width - ($this->pedding * 2);
				$position['height'] = $pixel;
				break;
		}
		return $position;
	}
	
	private function _setStep($step)
	{
		if ($step > $this->max)
			$step = $this->max;
		if ($step < $this->min)
			$step = $this->min;
		$this->step = $step;
	}
	
	// public functions
	public function setFrame($width = 0, $height = 0)
	{
		$this->frame = array (
			'show' => true,
			'left' => 20,
			'top' => 35,
			'width' => $this->width + 6,
			'height' => 'auto',
			'color' => '#c0c0c0',
			'border' => 2,
			'brd_color' => '#dfdfdf #404040 #404040 #dfdfdf'
		);
		
		if ($width > 0)
			$this->frame['width'] = $width;
		if ($height > 0)
			$this->frame['height'] = $height;
	}
	
	public function addLabel($type, $name, $value = '&nbsp;')
	{
		switch($type)
		{
			case 'text':
				$this->label[$name] = array(
					'type' => 'text',
					'value' => $value,
					'left' => 0, // keep all to the left in box
					'top' => 2, // default top is 2px
					'width' => $this->width,
					'height' => 0,
					'align' => 'left',
					'font-size' => 11,
					'font-family' => 'Verdana, Tahoma, Arial',
					'font-weight' => 'normal',
					'color' => '#000000',
					'bgr_color' => ''
				);
				break;
			case 'button':
				$this->label[$name] = array(
					'type' => 'button',
					'value' => $value,
					'action' => '',
					'target' => 'self',
					'left' => 5,
					'top' => 5,
					'width' => 0,
					'height' => 0,
					'align' => 'center',
					'font-size' => 11,
					'font-family' => 'Verdana, Tahoma, Arial',
					'font-weight' => 'normal',
					'color' => '#000000',
					'bgr_color' => ''
				);
				break;
			case 'step':
				$this->label[$name] = array(
					'type' => 'step',
					'value' => $value,
					'left' => $this->left + 5,
					'top' => $this->top + 5,
					'width' => 10,
					'height' => 0,
					'align' => 'right',
					'font-size' => 11,
					'font-family' => 'Verdana, Tahoma, Arial',
					'font-weight' => 'normal',
					'color' => '#000000',
					'bgr_color' => ''
				);
				break;
			case 'percentlbl':
			case 'percent':
				// check font size
				if ($this->height <= 11)
					$font_size = $this->height - 1;
				else
					$font_size = 11;
				$this->label[$name] = array(
					'type' => $type, // either percent or percentlbl
					'value' => $value,
					'left' => false,
					'top' => round(($this->height - $font_size) / log($this->height - $font_size, 7), 0) - $this->pedding,
					'width' => $this->width,
					'height' => 0,
					'align' => 'center',
					'font-size' => $font_size,
					'font-family' => 'sans-serif',
					'font-weight' => 'normal',
					'color' => '#000000',
					'bgr_color' => ''
				);
//				print "THIS[$name]: ".$this->label[$name]['left']." | ".$this->label[$name]['width']."<br>";
				break;
			case 'crossbar':
				$this->label[$name] = array(
					'type' => 'crossbar',
					'value' => $value,
					'left' => $this->left + ($this->width / 2),
					'top' => $this->top - 16,
					'width' => 10,
					'height' => 0,
					'align' => 'center',
					'font-size' => 11,
					'font-family' => 'Verdana, Tahoma, Arial',
					'font-weight' => 'normal',
					'color' => '#000000',
					'bgr_color' => ''
				);
				break;
		}
	}
	
	public function addButton($name, $value, $action, $target = 'self')
	{
		$this->addLabel('button', $name, $value);
		$this->label[$name]['action'] = $action;
		$this->label[$name]['target'] = $target;
	}
	
	public function setLabelPosition($name, $left, $top, $width, $height, $align='')
	{
//		print "SET POSITION[$name]: $left<br>";
		// if this is percent, we ignore anything, it is auto positioned
		if ($this->label[$name]['type'] != 'percent')
		{

			foreach (array('top', 'left', 'width', 'height') as $pos_name)
				if ($$pos_name !== false)
					$this->label[$name][$pos_name] = intval($$pos_name);

			if ($align != '')
				$this->label[$name]['align'] = $align;
		}	
		// init
		if ($this->status != 'new')
		{
			$output = '<script type="text/JavaScript">';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.top="'.$this->label[$name]['top'].'px";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.left="'.$this->label[$name]['left'].'px";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.width="'.$this->label[$name]['width'].'px";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.height="'.$this->label[$name]['height'].'px";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.align="'.$this->label[$name]['align'].'";';
			$output .= '</script>'."\n";
			echo $output;
			$this->_flushCache();
		}
	}
	
	public function setLabelColor($name, $color)
	{
		$this->label[$name]['color'] = $color;
		if ($this->status != 'new')
		{
			echo '<script type="text/JavaScript">document.getElementById("plbl'.$name.$this->code.'").style.color="'.$color.'";</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function setLabelBackground($name, $color)
	{
		$this->label[$name]['bgr_color'] = $color;
		if ($this->status != 'new')
		{
			echo '<script type="text/JavaScript">document.getElementById("plbl'.$name.$this->code.'").style.background="'.$color.'";</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function setLabelFont($name, $size, $family = '', $weight = '')
	{
		// just in case if it is too small
		if (intval($size) < 0)
			$size = 11;
		// if this is percent, the size is not allowed to be bigger than the bar size - 5px
		if ($this->label[$name]['type'] == 'percent' && intval($size) >= $this->height)
			$size = $this->height - 1;
		// position the label new if this is percent
		if ($this->label[$name]['type'] == 'percent')
			$this->label[$name]['top'] = round(($this->height - intval($size)) / log($this->height - intval($size), 7), 0) - $this->pedding;
//		print "HEIGHT: ".$this->height.", Size: ".intval($size).", Pedding: ".$this->pedding.", Calc: ".round($this->height - intval($size)).", Log: ".log($this->height - intval($size), 7)."<br>";
		// then set like usual
		$this->label[$name]['font-size'] = intval($size);
		if ($family != '')
			$this->label[$name]['font-family'] = $family;
		if ($weight != '')
			$this->label[$name]['font-weight'] = $weight;
		
		if ($this->status != 'new')
		{
			$output = '<script type="text/JavaScript">';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.font-size="'.$this->label[$name]['font-size'].'px";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.font-family="'.$this->label[$name]['font-family'].'";';
			$output .= 'document.getElementById("plbl'.$name.$this->code.'").style.font-weight="'.$this->label[$name]['font-weight'].'";';
			$output .= '</script>'."\n";
			echo $output;
			$this->_flushCache();
		}
	}
	
	public function setLabelValue($name, $value)
	{
		$this->label[$name]['value'] = $value;
//		print "NAME[$name], Status: ".$this->status.": ".$value."<Br>";
		if ($this->status != 'new')
		{
			echo '<script type="text/JavaScript">PBlabelText'.$this->code.'("'.$name.'","'.$this->label[$name]['value'].'");</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function setBarColor($color)
	{
		$this->color = $color;
		if ($this->status != 'new')
		{
			echo '<script type="text/JavaScript">document.getElementById("pbar'.$this->code.'").style.background="'.$color.'";</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function setBarBackground($color)
	{
		$this->bgr_color = $color;
		if ($this->status != 'new')
		{
			echo '<script type="text/JavaScript">document.getElementById("pbrd'.$this->code.'").style.background="'.$color.'";</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function setBarDirection($direction)
	{
		$this->direction = $direction;
		
		if ($this->status != 'new')
		{
			$this->position = $this->_calculatePosition($this->step);
			
			echo '<script type="text/JavaScript">';
			echo 'PBposition'.$this->code.'("left",'.$this->position['left'].');';
			echo 'PBposition'.$this->code.'("top",'.$this->position['top'].');';
			echo 'PBposition'.$this->code.'("width",'.$this->position['width'].');';
			echo 'PBposition'.$this->code.'("height",'.$this->position['height'].');';
			echo '</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function getHtml()
	{
		$html = '';
		$js = '';
		$html_button = '';
		
		$this->_setStep($this->step);
		$this->position = $this->_calculatePosition($this->step);

		if ($this->top || $this->left)
			$style_master = 'position:relative;top:'.$this->top.'px;left:'.$this->left.'px;width:'.($this->width + 10).'px;';
		$html = '<div id="pbm'.$this->code.'" style="'.$style_master.'background:'.$this->bgr_color_master.';">';
		$style_brd = 'width:'.$this->width.'px;height:'.$this->height.'px;background:'.$this->bgr_color.';';
		if ($this->border > 0)
			$style_brd .= 'border:'.$this->border.'px solid; border-color:'.$this->brd_color.'; -webkit-border-radius: 5px 5px 5px 5px; border-radius: 5px 5px 5px 5px; -webkit-shadow: 2px 2px 10px rgba(0, 0, 0, 0.25) inset; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.25) inset;';
		
		$style_bar = 'position:relative;width:'.$this->position['width'].'px;height:'.$this->position['height'].'px;background:'.$this->color.';';
		if ($this->position['top'] !== false)
			$style_bar .= 'top:'.$this->position['top'].'px;';
		if ($this->position['left'] !== false)
			$style_bar .= 'left:'.$this->position['left'].'px;';
		if ($this->border > 0)
			$style_bar .= '-webkit-border-radius: 5px 5px 5px 5px; border-radius: 5px 5px 5px 5px; -webkit-shadow: 2px 2px 10px rgba(0, 0, 0, 0.25) inset; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.25) inset;';
		
		if ($this->frame['show'] == true)
		{
			if ($this->frame['border'] > 0)
				$border = 'border:'.$this->frame['border'].'px solid;border-color:'.$this->frame['brd_color'].';margin-top:2px;-webkit-border-radius: 5px 5px 5px 5px; border-radius: 5px 5px 5px 5px;';
			$html .= '<div id="pfrm'.$this->code.'" style="width:'.$this->frame['width'].'px;height:'.$this->frame['height'].'px;'.$border.'background:'.$this->frame['color'].';">'."\n";
		}

		// temp write the bar here, we add that later, below all the html + progress %	
		$html_bar_top = '<div id="pbrd'.$this->code.'" style="'.$style_brd.($this->frame['show'] == true ? 'margin-left: 2px;margin-bottom:2px;' : '').'">'."\n";
		$html_bar_top .= '<div id="pbar'.$this->code.'" style="'.$style_bar.'">';
		// insert single percent there
		$html_bar_bottom = '</div></div>'."\n";
		
		$js .= 'function PBposition'.$this->code.'(item,pixel) {'."\n";
		$js .= ' pixel = parseInt(pixel);'."\n";
		$js .= ' switch(item) {'."\n";
		$js .= '  case "left": document.getElementById("pbar'.$this->code.'").style.left=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "top": document.getElementById("pbar'.$this->code.'").style.top=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "width": document.getElementById("pbar'.$this->code.'").style.width=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "height": document.getElementById("pbar'.$this->code.'").style.height=(pixel) + \'px\'; break;'."\n";
		$js .= ' }'."\n";
		$js .= '}'."\n";

//print "DUMP LABEL: <br><pre>".print_r($this->label, 1)."</pre><br>";
		foreach ($this->label as $name => $data)
		{
			// set what type of move we do
			$move_prefix = $data['type'] == 'button' ? 'margin' : 'padding';
			$style_lbl = 'position:relative;';
			if ($data['top'] !== false)
				$style_lbl .= $move_prefix.'-top:'.$data['top'].'px;';
			if ($data['left'] !== false)
				$style_lbl .= $move_prefix.'-left:'.$data['left'].'px;';
			$style_lbl .= 'text-align:'.$data['align'].';';
			if ($data['width'] > 0)
				$style_lbl .= 'width:'.$data['width'].'px;';
			if ($data['height'] > 0)
				$style_lbl .= 'height:'.$data['height'].'px;';
			
			if (array_key_exists('font-size', $data))
				$style_lbl .= 'font-size:'.$data['font-size'].'px;';
			if (array_key_exists('font-family', $data))
				$style_lbl .= 'font-family:'.$data['font-family'].';';
			if (array_key_exists('font-weight', $data))
				$style_lbl .= 'font-weight:'.$data['font-weight'].';';
			if (array_key_exists('bgr_color', $data) && ($data['bgr_color'] != ''))
				$style_lbl .= 'background:'.$data['bgr_color'].';';
			
			if (array_key_exists('type', $data))
			{
				switch ($data['type'])
				{
					case 'text':
						$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'margin-bottom:2px;">'.$data['value'].'</div>'."\n";
						break;
					case 'button':
						$html_button .= '<div><input id="plbl'.$name.$this->code.'" type="button" value="'.$data['value'].'" style="'.$style_lbl.'margin-bottom:5px;" onclick="'.$data['target'].'.location.href=\''.$data['action'].'\'" /></div>'."\n";
						break;
					case 'step':
						$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$this->step.'</div>'."\n";
						break;
					case 'percent':
						// only one inner percent
//						print "STYLE[$name]: ".$style_lbl."<br>";
						if (!$html_percent)
							$html_percent = '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'width:'.$data['width'].'px;line-height:1;text-shadow: 0 0 .2em white, 0 0 .5em white;">'.$this->_calculatePercent($this->step).'%</div>'."\n";
						break;
					case 'percentlbl':
						$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'width:'.$data['width'].'px;">'.$this->_calculatePercent($this->step).'%</div>'."\n";
						break;
					case 'crossbar':
						$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$data['value'].'</div>'."\n";
						
						$js .= 'function PBrotaryCross'.$name.$this->code.'() {'."\n";
						$js .= ' cross = document.getElementById("plbl'.$name.$this->code.'").firstChild.nodeValue;'."\n";
						$js .= ' switch(cross) {'."\n";
						$js .= '  case "--": cross = "\\\\"; break;'."\n";
						$js .= '  case "\\\\": cross = "|"; break;'."\n";
						$js .= '  case "|": cross = "/"; break;'."\n";
						$js .= '  default: cross = "--"; break;'."\n";
						$js .= ' }'."\n";
						$js .= ' document.getElementById("plbl'.$name.$this->code.'").firstChild.nodeValue = cross;'."\n";
						$js .= '}'."\n";
						break;
				}
			}
		}

		// write the progress bar + inner percent inside
		$html .= $html_bar_top;
		$html .= $html_percent;
		$html .= $html_bar_bottom;
		$html .= $html_button; // any buttons on bottom
		
		if (count($this->label) > 0)
		{
			
			$js .= 'function PBlabelText'.$this->code.'(name,text) {'."\n";
			$js .= ' name = "plbl" + name + "'.$this->code.'";'."\n";
			$js .= ' document.getElementById(name).innerHTML=text;'."\n";
			$js .= '}'."\n";
		}
		
		if ($this->frame['show'] == true)
		{
			$html .= '</div>'."\n";
		}
		
		$html .= '<script type="text/JavaScript">'."\n";
		$html .= $js;
		$html .= '</script>'."\n";

		$html .= '</div>';
		
		return $html;
	}
	
	public function show()
	{
		$this->status = 'show';
		echo $this->getHtml();
		$this->_flushCache();
	}
	
	public function moveStep($step)
	{
		$last_step = $this->step;
		$this->_setStep($step);
		
		$js = '';
		$new_position = $this->_calculatePosition($this->step);
		if ($new_position['width'] != $this->position['width'] && ($this->direction == 'right' || $this->direction == 'left'))
		{
			if ($this->direction=='left')
			{
				$js .= 'PBposition'.$this->code.'("left",'.$new_position['left'].');';
			}
			$js .= 'PBposition'.$this->code.'("width",'.$new_position['width'].');';
		}
		if ($new_position['height'] != $this->position['height'] && ($this->direction == 'up' || $this->direction == 'down'))
		{
			if ($this->direction=='up')
			{
				$js .= 'PBposition'.$this->code.'("top",'.$new_position['top'].');';
			}
			$js .= 'PBposition'.$this->code.'("height",'.$new_position['height'].');';
		}
		$this->position = $new_position;
		foreach($this->label as $name => $data)
		{
			if (array_key_exists('type', $data))
			{
				switch($data['type'])
				{
					case 'step':
						if ($this->step != $last_step)
						{
							$js .= 'PBlabelText'.$this->code.'("'.$name.'","'.$this->step.'/'.$this->max.'");';
						}
						break;
					case 'percentlbl':
					case 'percent':
						$percent = $this->_calculatePercent($this->step);
						if ($percent != $this->_calculatePercent($last_step))
						{
							$js .= 'PBlabelText'.$this->code.'("'.$name.'","'.$percent.'%");';
						}
						break;
					case 'crossbar':
						$js .= 'PBrotaryCross'.$name.$this->code.'();';
						break;
				}
			}
		}
		if ($js != '')
		{
			echo '<script type="text/JavaScript">'.$js.'</script>'."\n";
			$this->_flushCache();
		}
	}
	
	public function moveNext()
	{
		$this->moveStep($this->step + 1);
	}
	
	public function moveMin()
	{
		$this->moveStep($this->min);
	}
	
	public function hide()
	{
		if ($this->status == 'show')
		{
			$this->status = 'hide';
			
			$output = '<script type="text/JavaScript">';
			$output .= 'document.getElementById("pbm'.$this->code.'").style.visibility="hidden";document.getElementById("pbm'.$this->code.'").style.display="none";';
			$output .= '</script>'."\n"; 
			echo $output;
			$this->_flushCache();
		}
	}
	
	public function unhide()
	{
		if ($this->status == 'hide')
		{
			$this->status = 'show';
			
			$output = '<script type="text/JavaScript">';
			$output .= 'document.getElementById("pbm'.$this->code.'").style.visibility="visible";document.getElementById("pbm'.$this->code.'").style.visibility="block";';
			$output .= '</script>'."\n";
			echo $output;
			$this->_flushCache();
		}
	}
	
}
?>
