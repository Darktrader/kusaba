# Usage #
#fortune in name or email field

# Customization #
Add your own fortunes by adding them to the fortunes config in module\_settings.  Fortunes are separated by pipes.

# SQL #
```
INSERT INTO `module_settings` (`module`, `key`, `value`, `type`) VALUES ('fortune', 'boards', 'b|test', 'string'), ('fortune', 'fortunes', '<font color="#B604A2"><b>Your fortune: Godly Luck</b></font>|<font color="indigo"><b>Your fortune: Outlook good</b></font>|<font color="dodgerblue"><b>Your fortune: You will meet a dark handsome stranger</b></font>|<font color="darkorange"><b>Your fortune: Good Luck</b></font>|<font color="royalblue"><b>Your fortune: Better not tell you now</b></font>|<font color="deeppink"><b>Your fortune: Reply hazy; try again</b></font>|<font color="lime"><b>Your fortune: Very Bad Luck</b></font>|<font color="lime"><b>Your fortune: Good news will come to you by mail</b></font>|<font color="#BFC52F"><b>Your fortune: Average Luck</b></font>', 'string');
```

**Replace 'b|test' with your own list of boards you wish to allow this module to function, separated by pipes**

# fortune.php #
```
<?php
/*
 * This file is part of Trevorchan.
 *
 * Trevorchan is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Trevorchan is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Trevorchan; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * +------------------------------------------------------------------------------+
 * Fortune Module (created by tj9991)
 * +------------------------------------------------------------------------------+
 * Give your posters some good luck (or bad).
 * +------------------------------------------------------------------------------+
 */

/* Module initialization */
function fortune_init() {
	global $hooks;
	
	$hooks['posting'][] = 'fortune';
}

/* Is this module authorized to be used right now? */
function fortune_authorized($board) {
	$boards_authorized = explode('|', module_setting_get('fortune', 'boards'));
	
	if (in_array($board, $boards_authorized)) {
		return true;
	} else {
		return false;
	}
}

function fortune_info() {
	$info = array();
	$info['type']['board-specific'] = true;
	
	return $info;
}

function fortune_settings() {
	$settings = array();
	
}

function fortune_help() {
	$output = 'Fortune:  Give your posters some good luck (or bad).  To use: Place #fortune in the name or email field.';
	
	return $output;
}

function fortune_process_posting($post) {
	if (isset($post['board'])) {
		if (!sillynames_authorized($post['board'])) {
			return $post;
		}
	}
	if (substr(strtolower($post['name']), 0, 8) == '#fortune' || substr(strtolower($post['email']), 0, 8) == '#fortune') {
		$post['message'] = fortune__get_fortune() . '<br><br>' . $post['message'];
		if (substr(strtolower($post['name']), 0, 8) == '#fortune') {
			$post['name'] = '';
			$post['name_save'] = false;
		} elseif (substr(strtolower($post['email']), 0, 8) == '#fortune') {
			$post['email'] = '';
			$post['email_save'] = false;
		}
	}
	
	return $post;
}

function fortune__get_fortune() {
	$fortunes = explode('|', module_setting_get('fortune', 'fortunes'));
	$fortune_index = rand(0, (count($fortunes) - 1));
	$fortune = $fortunes[$fortune_index];
	
	return $fortune;
}

?>
```