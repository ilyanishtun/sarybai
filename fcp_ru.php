<?php
// Fcp.Commentator -- stand-alone web page commenting script.
// Version: 2007-10-27
// Copyright (C) 2007 Felix Pleşoianu <felixp7@yahoo.com>

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.

// Usage:
// * create a directory to hold the comments;
// * make sure COMMENT_DIR points to it;
// * make sure the Web server has full rights on it (read/write/execute);
// * include this script in your page with something like
//     include 'fcp.commentator.php';
// * add CSS according to taste.

if (!defined('COMMENT_DIR'))
	define('COMMENT_DIR', '/comments/');//��� ����������, ��� ����������� ���������

function read_comments() {
	if (!is_dir(COMMENT_DIR) || !is_readable(COMMENT_DIR))
		return false;
	$file_list = glob(COMMENT_DIR . '*.txt');
	$comments = array();
	foreach ($file_list as $file_name) {
		if (($data = file($file_name)) !== false) {
			$comments[] = unserialize_comment($data);
		}
	}
	return $comments;
}

// Decode a comment in MIME format.
function unserialize_comment($data) {
	$comment = array();
	while (strlen($line = chop(array_shift($data))) > 0) {
		$matches = array();
		if (!preg_match('/^([\w-]+):\s*(.*)$/', $line, $matches))
			return false;
		$comment[$matches[1]] = $matches[2];
	}
	$comment['datetime'] = parse_iso_datetime($comment['datetime']);
	$comment['comment'] = join("\n", array_map('chop', $data));
	return $comment;
}

function parse_iso_datetime($string) {
	$matches = array();
	$re = '/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})$/';
	if (preg_match($re, $string, $matches)) {
		return array_slice($matches, 1);
	} else {
		return false;
	}
}

function post_comment($data, &$errors) {
	if (!is_dir(COMMENT_DIR) || !is_writeable(COMMENT_DIR))
		return false;
	$data['ip'] = $_SERVER['REMOTE_ADDR'];
	$data['datetime'] = strftime('%Y%m%dT%H%M%S');
	if (!is_valid_comment($data, $errors)) return false;
	$comment = serialize_comment($data);
	$filename = COMMENT_DIR	. $data['datetime'] . '-'
		. ip2long($data['ip']) . '.txt';
	if (($handle = fopen($filename, "w")) === false)
		return false;
	if (!fwrite($handle, $comment)) return false;
	fclose($handle);
	return true;
}

function is_valid_comment($data, &$errors) {
	$valid_mail = is_valid_e_mail($data['e_mail'], $errors);
	$valid_message = is_valid_message($data['message'], $errors);
	$right_answer = is_right_answer(
		$data['test_question'], $data['test_answer'], $errors);
	return $valid_mail && $valid_message && $right_answer;
}

function is_valid_e_mail($string, &$errors) {
	$is_valid = preg_match('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $string);
	if (!$is_valid) $errors[] = '������� ���������� e-mail';
	return $is_valid;
}

function is_valid_message($string, &$errors) {
	$is_valid = strlen($string) > 0;
	if (!$is_valid) $errors[] = '�� �� �������� ����� ���������';
	return $is_valid;
}

function is_right_answer($question, $answer, &$errors) {
	if (!preg_match('/\d [\+\*\-] \d/', $question)) {
		$errors[] = '��������, �� - �����';
		return false;
	} elseif (eval('return ' . $question . ';') != intval($answer)) {
		$errors[] = '�������� ���������� �����';
		return false;
	} else {
		return true;
	}
}

// Encode comment in a MIME-like format.
// Assume data is already validated.
function serialize_comment($data) {
	$message = '';
	$fields = array('ip', 'datetime', 'name', 'e_mail');
	foreach ($fields as $field) {
		if (get_magic_quotes_gpc())
			$data[$field] = stripslashes($data[$field]);
		$message .= $field . ': ' . $data[$field] . "\r\n";
	}
	if (get_magic_quotes_gpc())
		$data['message'] = stripslashes($data['message']);
	$message .= "\r\n" . $data['message'];
	return $message;
}

function format_datetime($datetime) {
	if (!is_array($datetime)) return '';
	return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
		$datetime[0], $datetime[1], $datetime[2],
		$datetime[3], $datetime[4], $datetime[5]);
}

if (!isset($_POST['name']))
	$_POST['name'] = '';
if (!isset($_POST['e_mail']))
	$_POST['e_mail'] = '';
if (!isset($_POST['message']))
	$_POST['message'] = '';
if (isset($_POST['post_comment'])) {
	$errors = array();
	post_comment($_POST, $errors)
		or $errors[] = '����������� �� ��� ��������';
}
$comments = read_comments();
$ops = array('+', '-', '*');
$test_human = sprintf('%d %s %d',
	rand(1, 9), $ops[array_rand($ops)], rand(1, 9));
?>
<div class="fcp-comments">
<?php if ($comments !== false): ?>
 <?php if (count($comments) > 0): ?>
  <?php foreach ($comments as $comment): ?>
   <div class="comment">
    <?php print '---------<br>'.htmlspecialchars(
     $comment['comment']).'<br>---------<br>'; ?>
     <?php printf(
	 '�������:
	 <font color="#FF0000"><b>%s</b></font> ���������: <font color="#0000FF">%s</font>',
     htmlspecialchars($comment['name']),
     format_datetime($comment['datetime'])); ?>
   </div>
  <?php endforeach; ?>
 <?php else: ?>
  <p>�������� ��� ����������� ������.</p>
 <?php endif; ?>
<?php else: ?>
 <p>Can't read comments.</p>
<?php endif; ?>

<?php if (isset($_POST['post_comment'])): ?>
 <div id="messages">
  <?php if (count($errors) > 0): ?>
   <ul class="errors">
    <?php foreach ($errors as $error): ?>
     <li><?php print $error; ?></li>
    <?php endforeach; ?>
   </ul>
  <?php else: ?>
   <p>����������� ��������</p>
  <?php endif; ?>
 </div>
<?php endif; ?>

<?php if (is_dir(COMMENT_DIR) && is_writeable(COMMENT_DIR)): ?>
<form method="post" action="<?php print $_SERVER['PHP_SELF']; ?>#messages">
 <table>
  <b><caption>�������� �����������</caption></b>
  <tr>
   <th>���:</th>
   <td><input name="name" value="<?php print $_POST['name']; ?>" /></td>
  </tr>
  <tr>
   <th>��� e-mail (����� �����):</th>
   <td><input type="email" name="e_mail" required
    value="<?php print $_POST['e_mail']; ?>" /></td>
  </tr>
 
   <th>���������:</th>
   <td>
    <textarea name="message" required><?php
     print htmlspecialchars($_POST['message']); ?></textarea>
   </td>
  </tr>
 </table>
 <p>��������, ��� �� �� ���. ������� �����
  <?php print $test_human; ?>?
  <input type="hidden" name="test_question"
   value="<?php print $test_human; ?>" />
  <input name="test_answer" size="3" required />
 </p>
 <input type="submit" name="post_comment" value="���������" />
</form>
<?php else: ?>
 <p>����������� ���������.</p>
<?php endif; ?>
</div>
