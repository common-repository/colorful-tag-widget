<?php
/*
Plugin Name: Colorful Tag Cloud Widget
Plugin URI: http://www.techxperts.co.in/wordpress/plugins/category-cloud-widget.html
Description: Adds a sidebar widget to display the categories as a tag cloud.
Author: Techxperts
Version: 0.3
Author URI: http://www.techxperts.co.in/
Email : support@techxperts.co.in
*/

/**
 * ColorfullTagCloud Widget Class
 */
class ColorfullTagCloud extends WP_Widget 
{    
	/** constructor */
    function ColorfullTagCloud() 
	{
		$widget_ops = array('description' => 'ColorfulTagCloud');
		$control_ops = array('width' => 300, 'height' => 500);
		parent::WP_Widget(false,$name='ColorfullTagCloud',$widget_ops,$control_ops);
        //parent::WP_Widget(false, $name = 'ColorfullTagCloud');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) 
	{
        extract($args);
		$defaults = array('small' => 50, 'big' => 150, 'unit' => '%', 'align' => 'justify', 'orderby' => 'name', 'order' => 'ASC', 'min' => 0, 'color1' => 'ff0000', 'color2' => 'ff0000', 'color3' => 'ff0000', 'color4' => 'ff0000', 'color5' => 'ff0000', 'hide-empty' => 1, 'hide-poweredby' => 1, 'hide-cats' => 1, 'hide-tags' => 1);
		$options = (array) get_option('ColorfullTagCloud');
		//$default = get_option('ColorfullTagCloud');
		
		foreach ( $defaults as $key => $value )
			if ( !isset($options[$key]) )
				$options[$key] = $defaults[$key];

		echo $before_widget;
		
		// omit title if not specified
		if ($options['title'] != '')
			echo $before_title . $options['title'] . $after_title;
		
		if ($options['exclude'] != '')
			$exclude = '&exclude=' . $options['exclude'];
				
		$hide_empty = '&hide_empty=' . $options['hide-empty'];
		
		// check which version of wp is being used
		if ( function_exists('get_categories') )
		{
			// new version of wp (2.1+)
			$cats = get_categories("style=cloud&show_count=1&use_desc_for_title=0$exclude&hierarchical=0$hide_empty");
			foreach ($cats as $cat)
			{
				$catlink = get_category_link( $cat->cat_ID );
				$catname = $cat->cat_name;
				$count = $cat->category_count;
								
				if ($count >= $options['min'])
				{
					$counts{$catname} = $count;
					$catlinks{$catname} = $catlink;
				}
			}
		}
		else
		{
			// old version of wp (pre-2.1)
			$cats = wp_list_cats("list=0&sort_column=name&optioncount=1&use_desc_for_title=0$exclude&recurse=1&hierarchical=0$hide_empty");
			
			$cats = explode("<br />\n", $cats);
			foreach ($cats as $cat)
			{
				$regs = array(); // initialise the regs array
				eregi("a href=\"(.+)\" ", $cat, $regs);
				$catlink = $regs[1];
				$cat = trim(strip_tags($cat));
				eregi("(.*) \(([0-9]+)\)$", $cat, $regs);
				$catname = $regs[1];
				$count = $regs[2];
				if ($count >= $options['min'])
				{
					$counts{$catname} = $count;
					$catlinks{$catname} = $catlink;
				}
			}
		}
		
		$spread = max($counts) - min($counts); 
		if ($spread <= 0) { $spread = 1; };
		$fontspread = $options['big'] - $options['small'];
		$fontstep = $spread / $fontspread;
		if ($fontspread <= 0) { $fontspread = 1; }

		echo '<p class="catcloud" style="text-align:'.$options['align'].';">';
		
		if ('count' == $options['orderby'])
		{
			if ('DESC' == $options['order'])
				arsort($counts);
			else
				asort($counts);
		}
		elseif ('name' == $options['orderby'])
		{
			if ('DESC' == $options['order'])
				uksort($counts, create_function('$a, $b', 'return -(strnatcasecmp($a, $b));'));
			else
				uksort($counts, 'strnatcasecmp');
		}
		if ($options['hide-cats'])
		{
			if($options['item_select'] == 1)
				$arr = array($options['color1']);

			if($options['item_select'] == 2)
				$arr = array($options['color1'],$options['color2']);

			if($options['item_select'] == 3)
				$arr = array($options['color1'],$options['color2'],$options['color3']);

			if($options['item_select'] == 4)
				$arr = array($options['color1'],$options['color2'],$options['color3'],$options['color4']);

			if($options['item_select'] == 5)
				$arr = array($options['color1'],$options['color2'],$options['color3'],$options['color4'],$options['color5']);
			
			shuffle($arr);
			//$color = array_rand($arr);
			$i=0;
			foreach ($counts as $catname => $count)
			{
				if($i == count($arr))
				{
					shuffle($arr);
					$i=0;
				}
				$catlink = $catlinks{$catname};
				echo "\n<a href=\"$catlink\" title=\"$count posts filed under $catname\" style=\"color:#".$arr[$i].";font-size:".($options['small'] + ceil($count/$fontstep)).$options['unit']."\">$catname</a> ";
				$i++;
			}
		}
		if ($options['hide-tags'])
		{
			if($options['item_select'] == 1)
				$arr = array($options['color1']);

			if($options['item_select'] == 2)
				$arr = array($options['color1'],$options['color2']);

			if($options['item_select'] == 3)
				$arr = array($options['color1'],$options['color2'],$options['color3']);

			if($options['item_select'] == 4)
				$arr = array($options['color1'],$options['color2'],$options['color3'],$options['color4']);

			if($options['item_select'] == 5)
				$arr = array($options['color1'],$options['color2'],$options['color3'],$options['color4'],$options['color5']);
			
			shuffle($arr);
			//$color = array_rand($arr);
			$i=0;
			$tags = (array) get_tags('get=all');		
			if ( !empty($tags) ) foreach ( (array) $tags as $tag ) 
			{
				if($i == count($arr))
				{
					shuffle($arr);
					$i=0;
				}
				$catlink = get_tag_link( $tag->term_id );			
				echo "\n<a href=\"$catlink\" title=\"Tag : $tag->name\" style=\"color:#".$arr[$i].";font-size:".($options['small'] + ceil($count/$fontstep)).$options['unit']."\">$tag->name</a> ";
				$i++;
			}
		}	
		if (!$options['hide-cats'] && !$options['hide-tags'])	
			echo 'Please cofigure properly...';
			
		echo '</p>';
		
		if (!$options['hide-poweredby'])
			echo '<p style="font-size:xx-small;font-style:italic;text-align:right;"><a href="http://www.techxperts.co.in/wordpress/plugins/category-cloud-widget/">-- Powered by Techxperts</a></p>';
		
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) 
	{
		return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) 
	{
        $options = $newoptions = get_option('ColorfullTagCloud');
		if ( $_POST['catcloud-submit'] )
		{
			$newoptions['title'] = strip_tags(stripslashes($_POST['catcloud-title']));
			$newoptions['item_select'] = strip_tags(stripslashes($_POST['item_select']));			
			
			$newoptions['small'] = ($_POST['catcloud-small'] != '') ? (int) $_POST['catcloud-small'] : 50;

			if($_POST['item_select'] == 1)
			{
				$newoptions['color1'] = ($_POST['color1'] != '') ? $_POST['color1'] : 'ff0000';
			}
			if($_POST['item_select'] == 2)
			{
				$newoptions['color1'] = ($_POST['color1'] != '') ? $_POST['color1'] : 'ff0000';
				$newoptions['color2'] = ($_POST['color2'] != '') ? $_POST['color2'] : 'ff0000';
			}
			if($_POST['item_select'] == 3)
			{
				$newoptions['color1'] = ($_POST['color1'] != '') ? $_POST['color1'] : 'ff0000';
				$newoptions['color2'] = ($_POST['color2'] != '') ? $_POST['color2'] : 'ff0000';
				$newoptions['color3'] = ($_POST['color3'] != '') ? $_POST['color3'] : 'ff0000';
			}
			if($_POST['item_select'] == 4)
			{
				$newoptions['color1'] = ($_POST['color1'] != '') ? $_POST['color1'] : 'ff0000';
				$newoptions['color2'] = ($_POST['color2'] != '') ? $_POST['color2'] : 'ff0000';
				$newoptions['color3'] = ($_POST['color3'] != '') ? $_POST['color3'] : 'ff0000';
				$newoptions['color4'] = ($_POST['color4'] != '') ? $_POST['color4'] : 'ff0000';
			}
			if($_POST['item_select'] == 5)
			{
				$newoptions['color1'] = ($_POST['color1'] != '') ? $_POST['color1'] : 'ff0000';
				$newoptions['color2'] = ($_POST['color2'] != '') ? $_POST['color2'] : 'ff0000';
				$newoptions['color3'] = ($_POST['color3'] != '') ? $_POST['color3'] : 'ff0000';
				$newoptions['color4'] = ($_POST['color4'] != '') ? $_POST['color4'] : 'ff0000';
				$newoptions['color5'] = ($_POST['color5'] != '') ? $_POST['color5'] : 'ff0000';
			}
			
			$newoptions['big'] = ($_POST['catcloud-big'] != '') ? (int) $_POST['catcloud-big'] : 150;
			$newoptions['unit'] = ($_POST['catcloud-unit'] != '') ? $_POST['catcloud-unit'] : '%';
			$newoptions['align'] = ($_POST['catcloud-align'] != '') ? $_POST['catcloud-align'] : 'justify';
			$newoptions['orderby'] = ($_POST['catcloud-orderby'] != '') ? $_POST['catcloud-orderby'] : 'name';
			$newoptions['order'] = ($_POST['catcloud-order'] != '') ? $_POST['catcloud-order'] : 'ASC';
			$newoptions['min'] = ($_POST['catcloud-min'] != '') ? (int) $_POST['catcloud-min'] : 0;
			$newoptions['hide-empty'] = isset($_POST['catcloud-hide-empty']);
			$newoptions['hide-poweredby'] = isset($_POST['catcloud-hide-poweredby']);
			$newoptions['hide-tags'] = isset($_POST['catcloud-hide-tags']);
			$newoptions['hide-cats'] = isset($_POST['catcloud-hide-cats']);			
			$exclude_cats = explode(',', trim(strip_tags(stripslashes($_POST['catcloud-exclude']))));
			
			// loop through each excluded cat id, check that it is numeric, otherwise omit
			$exclude = '';
			if ( count($exclude_cats) )
			{
				foreach ($exclude_cats as $exclude_cat)
				{
					$exclude_cat = trim($exclude_cat);
					if ( is_numeric($exclude_cat) )
						$exclude .= "$exclude_cat,";
				}
			}
			$newoptions['exclude'] = ($exclude != '') ? substr($exclude, 0, -1) : '';
		}
		if ( $options != $newoptions )
		{
			$options = $newoptions;
			update_option('ColorfullTagCloud', $options);
		}
		$hide_empty = $options['hide-empty'] ? 'checked="checked"' : '';
		$hide_tags = $options['hide-tags'] ? 'checked="checked"' : '';
		$hide_cats = $options['hide-cats'] ? 'checked="checked"' : '';		
		$hide_poweredby = $options['hide-poweredby'] ? 'checked="checked"' : '';
		?>
        <script language="javascript">
			function Check(th)
			{
				if(th == 0)
				{
					$(".message_display1").hide(); 
					$(".message_display2").hide(); 
					$(".message_display3").hide(); 
					$(".message_display4").hide(); 
					$(".message_display5").hide(); 
				}
				if(th == 1)
				{
					$(".message_display1").show(); 
					$(".message_display2").hide(); 
					$(".message_display3").hide(); 
					$(".message_display4").hide(); 
					$(".message_display5").hide(); 
				}
				if(th == 2)
				{
					$(".message_display2").show(); 
					$(".message_display3").hide(); 
					$(".message_display1").hide(); 
					$(".message_display4").hide(); 
					$(".message_display5").hide(); 
				}				
				if(th == 3)
				{
					$(".message_display3").show(); 
					$(".message_display1").hide(); 
					$(".message_display2").hide(); 
					$(".message_display4").hide(); 
					$(".message_display5").hide(); 
				}				
				if(th == 4)
				{
					$(".message_display4").show(); 
					$(".message_display1").hide(); 
					$(".message_display2").hide(); 
					$(".message_display3").hide(); 
					$(".message_display5").hide(); 
				}				
				if(th == 5)
				{
					$(".message_display5").show(); 
					$(".message_display1").hide(); 
					$(".message_display2").hide(); 
					$(".message_display3").hide(); 
					$(".message_display4").hide(); 
				}				
			}
		</script>
        <div style="text-align:right">
        	<?php echo '<script type="text/javascript" src="'.constant("WP_PLUGIN_URL").'/colorfulltagcloud/jquery-latest.js"></script>';?>
        	<?php echo '<script type="text/javascript" src="'.constant("WP_PLUGIN_URL").'/colorfulltagcloud/jscolor.js"></script>';?>
            <label for="catcloud-title" style="line-height:35px;display:block;">widget title: <input type="text" id="catcloud-title" name="catcloud-title" value="<?php echo htmlspecialchars($options['title']); ?>" /></label>
            <label for="catcloud-small" style="line-height:35px;display:block;">no of color picker: 
            <select class="item_select" name="item_select" onchange="Check(this.value)"> 
                <option value="0">Select Colors</option> 
                <option value="1" <?php if($options['item_select'] == 1) {?> selected="selected" <?php } ?>>Color1</option> 
                <option value="2" <?php if($options['item_select'] == 2) {?> selected="selected" <?php } ?>>Color2</option> 
                <option value="3" <?php if($options['item_select'] == 3) {?> selected="selected" <?php } ?>>Color3</option> 
                <option value="4" <?php if($options['item_select'] == 4) {?> selected="selected" <?php } ?>>Color4</option> 
                <option value="5" <?php if($options['item_select'] == 5) {?> selected="selected" <?php } ?>>Color5</option> 
            </select>
            </label>
            <?php if($options['item_select'] == 1) {?> 
        	<div class="message_display1">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
            </div>
            <?php
            } 
			else
			{
			?>
            <div class="message_display1" style="display:none;">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
            </div>
            <?
			}
			?>
            <?php if($options['item_select'] == 2) {?> 
            <div class="message_display2">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
            </div>
            <?php
            } 
			else
			{
			?>
            <div class="message_display2" style="display:none;">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
            </div>
            <?
			}
			?>
            
            <?php if($options['item_select'] == 3) {?> 
            <div class="message_display3">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
            </div>
            <?php
            } 
			else
			{
			?>
            <div class="message_display3" style="display:none;">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
            </div>
            <?
			}
			?>
            
            <?php if($options['item_select'] == 4) {?> 
            <div class="message_display4">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
                <label for="catcloud-big" style="line-height:35px;display:block;">color4: <input class="color" value="<?php echo $options['color4']; ?>" name="color4"></label>
            </div>
            <?php
            } 
			else
			{
			?>
            <div class="message_display4" style="display:none;">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
                <label for="catcloud-big" style="line-height:35px;display:block;">color4: <input class="color" value="<?php echo $options['color4']; ?>" name="color4"></label>
            </div>
            <?
			}
			?>
            
            <?php if($options['item_select'] == 5) 
			{?>
            <div class="message_display5">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
                <label for="catcloud-big" style="line-height:35px;display:block;">color4: <input class="color" value="<?php echo $options['color4']; ?>" name="color4"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color5: <input class="color" value="<?php echo $options['color5']; ?>" name="color5"></label>
            </div>
            <?php
            } 
			else
			{
			?>
            <div class="message_display5" style="display:none;">
	            <label for="catcloud-big" style="line-height:35px;display:block;">color1: <input class="color" value="<?php echo $options['color1']; ?>" name="color1"></label>
           		<label for="catcloud-big" style="line-height:35px;display:block;">color2: <input class="color" value="<?php echo $options['color2']; ?>" name="color2"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color3: <input class="color" value="<?php echo $options['color3']; ?>" name="color3"></label>
                <label for="catcloud-big" style="line-height:35px;display:block;">color4: <input class="color" value="<?php echo $options['color4']; ?>" name="color4"></label>
	            <label for="catcloud-big" style="line-height:35px;display:block;">color5: <input class="color" value="<?php echo $options['color5']; ?>" name="color5"></label>
            </div>
            <?
			}
			?>
            
            <label for="catcloud-small" style="line-height:35px;display:block;">minimum font: <input type="text" id="catcloud-small" name="catcloud-small" value="<?php echo htmlspecialchars($options['small']); ?>" /></label>
            <label for="catcloud-big" style="line-height:35px;display:block;">maximum font: <input type="text" id="catcloud-big" name="catcloud-big" value="<?php echo $options['big']; ?>" /></label>

            <label for="catcloud-unit" style="line-height:35px;display:block;">which font unit would you like to use: <select id="catcloud-unit" name="catcloud-unit"><option value="%" <?php selected('%',$options['unit']); ?>>%</option><option value="px" <?php selected('px',$options['unit']); ?>>px</option><option value="pt" <?php selected('pt',$options['unit']); ?>>pt</option></select></label>
            <label for="catcloud-align" style="line-height:35px;display:block;">cloud alignment: <select id="catcloud-align" name="catcloud-align"><option value="left" <?php selected('left',$options['align']); ?>>left</option><option value="right" <?php selected('right',$options['align']); ?>>right</option><option value="center" <?php selected('center',$options['align']); ?>>center</option><option value="justify" <?php selected('justify',$options['align']); ?>>justify</option></select></label>
            <div style="line-height:35px;display:block;">
                <label for="catcloud-orderby">order by: <select id="catcloud-orderby" name="catcloud-orderby"><option value="count" <?php selected('count',$options['orderby']); ?>>count</option><option value="name" <?php selected('name',$options['orderby']); ?>>name</option></select></label>
                <label for="catcloud-order"><select id="catcloud-order" name="catcloud-order"><option value="ASC" <?php selected('ASC',$options['order']); ?>>asc</option><option value="DESC" <?php selected('DESC',$options['order']); ?>>desc</option></select></label>
            </div>
            <label for="catcloud-min" style="line-height:35px;display:block;">minimum # of posts: <input type="text" id="catcloud-min" name="catcloud-min" value="<?php echo $options['min']; ?>" /></label>
            <label for="catcloud-hide-empty" style="line-height:25px;display:block;">hide empty categories? <input class="checkbox" type="checkbox" <?php echo $hide_empty; ?> id="catcloud-hide-empty" name="catcloud-hide-empty" /></label></p>
            <label for="catcloud-poweredby" style="line-height:25px;display:block;">hide 'powered by ...' link? <input class="checkbox" type="checkbox" <?php echo $hide_poweredby; ?> id="catcloud-hide-poweredby" name="catcloud-hide-poweredby" /></label></p>
            <label for="catcloud-poweredby" style="line-height:25px;display:block;">want to display tags <input class="checkbox" type="checkbox" <?php echo $hide_tags; ?> id="catcloud-hide-tags" name="catcloud-hide-tags" /></label></p>
            <label for="catcloud-poweredby" style="line-height:25px;display:block;">want to display category <input class="checkbox" type="checkbox" <?php echo $hide_cats; ?> id="catcloud-hide-cats" name="catcloud-hide-cats" /></label></p>
            <label for="catcloud-exclude" style="line-height:35px;display:block;">categories ids to exclude (separated by commas): <textarea id="catcloud-exclude" name="catcloud-exclude" style="width:200px;height:24px;"><?php echo $options['exclude']; ?></textarea></label>
            <input type="hidden" name="catcloud-submit" id="catcloud-submit" value="1" />
        </div>
		<?php
    }
} // class ColorfullTagCloud
function ColorfullTagCloudInit() 
{
	//wp_enqueue_script('jsColor', WP_PLUGIN_URL . '/colorfulltagcloud/jscolor.js');
	//wp_enqueue_script('jQueryLatest', WP_PLUGIN_URL . '/colorfulltagcloud/jquery-latest.js');
	register_widget('ColorfullTagCloud');
}
add_action('widgets_init', 'ColorfullTagCloudInit');
?>