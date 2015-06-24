# Introduction #

I had been thinking about having something like a module system for quite some time, but it wasn't until recently that I decided to give it a shot.  At first I didn't like it at all, but once I rearranged some things and put in a new structure for flow I was happy.  If you have seen Drupal's module system, you will soon realize I have modeled it after that.

With Modules, people can write their own code which will **hook** into various places where data is exchanged inside of kusaba, such as making a post.

An easy example is a wordfilter module (even though kusaba has a built in wordfilter, this makes for a good example.)  The wordfilter's module file is included, its init() function is called, which results in the wordfilter being added to the **posting hook**.

By having an entry in the posting hook, the **processing function** will be called with the post data while a post is taking place.  The processing function is where the action takes place, and handles and returns the data it is given.  In the wordfilter's case, the processing function would only analyze the **message** data from the post, which it searches through and replaces words according to its settings.  It then returns the modified post.

Once the processing function returns its data, any other modules hooked into posting will be called with the data, and finally once all hooks have been called, the data is inserted into the database.

# A Full Example #

Here is a full, working module which will replace any occurrence of 'penis' in the message of a post being processed with a much more colorful version.

```
/* Module initialization */
function penis_init() {
	global $hooks;
	
	$hooks['posting'][] = 'penis';
}

function penis_info() {
	$info = array();
	$info['type']['board-specific'] = true;
	
	return $info;
}

function penis_settings() {
	$settings = array();
	
}

function penis_help() {
	$output = 'Penis Plugin:  A plugin which will convert the word "penis" into something more colorful, to brighten your day.';
	
	return $output;
}

function penis_process_posting($post) {
	$post['message'] = preg_replace_callback('/penis/i', 'penis__replace_callback', $post['message']);
	
	return $post;
}

function penis__replace_callback($matches) {
	$random_colors = penis__random_colors();
	$color_foreground = $random_colors[0];
	$color_background = $random_colors[1];
	
	return '<span style="color: ' . $color_foreground . '; background-color: ' . $color_background . '"><b>PENIS</b></span>';
}

function penis__random_colors() {
	$colors = array('red', 'black', 'yellow', 'green', 'blue');
	$color_foreground = '';
	$color_background = '';
	
	while ($color_foreground == $color_background) {
		$color_index = rand(0, (count($colors) - 1));
		$color_foreground = $colors[$color_index];
		
		$color_index = rand(0, (count($colors) - 1));
		$color_background = $colors[$color_index];
	}
	
	return array($color_foreground, $color_background);
}
```

Now we shall look at it piece by piece.

```
/* Module initialization */
function penis_init() {
	global $hooks;
	
	$hooks['posting'][] = 'penis';
}
```
This is the function called when the module is loaded.  Anything which needs to be carried out right off the bat is placed here, which includes hooking into the posting process.

```
function penis_info() {
	$info = array();
	$info['type']['board-specific'] = true;
	
	return $info;
}
```

This is the space which will tell kusaba what kind of module it is dealing with.  The info system is still being decided on, so this serves only as an idea at the moment, and is not used.

```
function penis_settings() {
	$settings = array();
	
}
```

Same as above.

```
function penis_help() {
	$output = 'Penis Plugin:  A plugin which will convert the word "penis" into something more colorful, to brighten your day.';
	
	return $output;
}
```

The text which will be displayed when an administrator views the module's entry in the module list.

```
function penis_process_posting($post) {
	$post['message'] = preg_replace_callback('/penis/i', 'penis__replace_callback', $post['message']);
	
	return $post;
}
```

This is the function which will be called during a posting.  The format for processing functions is modulename\_process\_hook($data).  In this case, the hook is posting.  This function should handle and return the data which was given to it.  In this case, it modifies the post's message using a replace with a callback, which is below.

```
function penis__replace_callback($matches) {
	$random_colors = penis__random_colors();
	$color_foreground = $random_colors[0];
	$color_background = $random_colors[1];
	
	return '<span style="color: ' . $color_foreground . '; background-color: ' . $color_background . '"><b>PENIS</b></span>';
}
```

Called with each occurrence of 'penis' in the message.  It calls the custom function below, which will create and return two random acceptable colors.  This function is not part of the module structure, and therefor has two underscores after its name, as does the color function.  The callback then returns the newly formatted occurrence of 'penis'.

```
function penis__random_colors() {
	$colors = array('red', 'black', 'yellow', 'green', 'blue');
	$color_foreground = '';
	$color_background = '';
	
	while ($color_foreground == $color_background) {
		$color_index = rand(0, (count($colors) - 1));
		$color_foreground = $colors[$color_index];
		
		$color_index = rand(0, (count($colors) - 1));
		$color_background = $colors[$color_index];
	}
	
	return array($color_foreground, $color_background);
}
```

With an array of acceptable colors, this will loop through until it has two different colors picked, and returns them.