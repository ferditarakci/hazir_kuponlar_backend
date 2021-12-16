<?php

require_once('inc/globals.php');

$json = array();

$json['status']  = true;
$json['errors'] = false;
$json['message'] = '';
$json['comments'] = array();

if (isset($_POST)) {

	if (!isset($_POST['ajax']))	return false;

	$type = @$_POST['type'];
	$subtype = @$_POST['subtype'];
	$coupon_id = @intval($_POST['coupon_id']);
	$user_id = @intval($_POST['user_id']);
	$comment_id = @intval($_POST['comment_id']);
	$parent_id = @intval($_POST['parent_id']);
	$comment = @security($_POST['comment']);

	if ($type != 'load') {
		if (!isset($user['id'])) {
			$json['status']  = false;
			$json['errors'] = true;

			if ($type == 'like' or $type == 'unlike') {
				$json['message'] = 'Üye girişi yapmadan beğeni yapamazsınız!';
			}

			if ($subtype == 'add' or $subtype == 'reply' or strlen($comment) < 5) {
				$json['message'] = 'Üye girişi yapmadan yorum yapamazsınız!';
			}
		} elseif ($coupon_id == 0 and $type != 'banned') {
			$json['status']  = false;
			$json['errors'] = true;
			$json['message'] = 'Kupon seçimi yapmadan yorum yapamazsınız!';
		} elseif (!(($type == 'save' and $subtype == 'reply') or ($type == 'like' or $type == 'unlike')) and $user_id != $user['id'] and !$superAdmin) {
			$json['status']  = false;
			$json['errors'] = true;
			$json['message'] = 'İşlem yetkiniz bulunmuyor.';
		} elseif ($type == 'save' and strlen($comment) < 5) {
			$json['status']  = false;
			$json['errors'] = true;
			$json['message'] = 'Lütfen bir yorum yazın.';
		}
	}

	if (!$json['errors']) {

		if ($type == 'load') {
			if ($subtype == 'list') {
				$json['comments'] = loadComments($coupon_id, $parent_id);
			} elseif ($subtype == 'edit') {
				$json['comments'] = loadComments($coupon_id, 0, $comment_id);
			}
		} else if ($type == 'save') {
			$db = openDB();

			if ($coupon_id > 0 and $user_id > 0 and ($subtype == 'reply' or $user_id == $user['id'] or $superAdmin)) {
				if (($subtype == 'add' or $subtype == 'reply') and $comment_id == 0) {

					$st = $db->prepare("INSERT INTO tk_comments (user_id, coupon_id, parent_id, datetime, comment) VALUES (:user_id, :coupon_id, :parent_id, :datetime, :comment)");
					$st->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
					$st->bindValue(':coupon_id', $coupon_id, PDO::PARAM_INT);
					$st->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
					$st->bindValue(':datetime', time(), PDO::PARAM_INT);
					$st->bindValue(':comment', $comment, PDO::PARAM_STR);
					$st->execute();

					$json['comments'] = loadComments($coupon_id, 0, $db->lastInsertId());

					if ($parent_id > 0) {
						$rs = $db->prepare("SELECT COUNT(id) FROM tk_comments WHERE result = 1 AND parent_id = :parent_id");
						$rs->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
						$rs->execute();

						$json['parent_total_reply'] = $rs->fetch(PDO::FETCH_NUM)[0];
					}

					$json['message'] = 'Yorumunuz eklendi.';
				} elseif ($subtype == 'edit' and $comment_id > 0) {

					$st = $db->prepare("UPDATE tk_comments SET edit_datetime = :edit_datetime, comment = :comment WHERE id = :comment_id AND user_id = :user_id");
					$st->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
					$st->bindValue(':user_id', $user_id, PDO::PARAM_INT);
					$st->bindValue(':edit_datetime', time(), PDO::PARAM_INT);
					$st->bindValue(':comment', $comment, PDO::PARAM_STR);
					$st->execute();

					$json['comments'] = loadComments($coupon_id, 0, $comment_id);
					$json['message'] = 'Yorumunuz düzenlendi.';
				}
			}
			$db = null;
		} else if ($type == 'delete' and $user_id > 0 and $comment_id > 0) {
			$db = openDB();

			$st = $db->prepare("UPDATE tk_comments SET result = 0 WHERE id = :comment_id AND user_id = :user_id");
			$st->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
			$st->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$st->execute();

			$json['message'] = 'Yorumunuz silindi.';
			$db = null;
		} else if ($type == 'banned' and $user_id > 0 and $superAdmin) {
			$db = openDB();

			$st = $db->prepare("UPDATE tk_users SET banned = 1 WHERE id = :user_id");
			$st->bindValue(':user_id', $user_id, PDO::PARAM_INT);
			$st->execute();

			$json['message'] = 'Kullanıcı engellendi.';
			$db = null;
		} else if ($type == 'like' or $type == 'unlike' and $comment_id > 0) {
			$db = openDB();

			if ($type == 'unlike') {
				$st = $db->prepare("DELETE FROM tk_comment_likes WHERE user_id = :user_id AND comment_id = :comment_id");
			} else {
				$st = $db->prepare("REPLACE INTO tk_comment_likes (user_id, comment_id) VALUES (:user_id, :comment_id)");
			}

			$st->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
			$st->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
			$st->execute();
			$json['comments'] = loadComments($coupon_id, 0, $comment_id);
			$db = null;
		}
	}
}

echo json_encode($json);


function loadComments($coupon_id = 0, $parent_id = 0, $comment_id = 0)
{
	global $superAdmin, $user, $type, $subtype;
	$data = array();

	// COMMENTS
	$query = "
		SELECT
			c.id as comment_id, c.coupon_id, c.parent_id, c.comment, c.datetime, c.user_id, u.first_name, u.last_name
			,(SELECT COUNT(id) FROM tk_comments WHERE result = 1 AND parent_id = c.id) As reply
			,(SELECT COUNT(id) FROM tk_comment_likes WHERE comment_id = c.id) As likes
		FROM tk_comments c
			JOIN tk_users u ON c.user_id = u.id
		WHERE c.result = 1 AND u.banned = 0
	";

	if ($comment_id == 0) {
		$query .= "
				AND c.parent_id = :parent_id
				AND c.coupon_id = :coupon_id
		";
	} else {
		$query .= "
				AND c.id = :comment_id
				AND c.coupon_id = :coupon_id
		";
	}

	$query .= "
		ORDER BY c.id DESC
	";

	$db = openDB();
	$rs = $db->prepare($query);
	$rs->bindValue(':coupon_id', $coupon_id, PDO::PARAM_INT);

	if ($comment_id == 0) {
		$rs->bindValue(':parent_id', $parent_id, PDO::PARAM_INT);
	} else {
		$rs->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
	}

	$rs->execute();

	$result = $rs->fetchAll(PDO::FETCH_ASSOC);

	$count = 0;
	foreach ($result as $row) {
		$dataThumb = array();
		$dataThumb["comment_id"] = $row["comment_id"];
		$dataThumb["coupon_id"] = $row["coupon_id"];
		$dataThumb["parent_id"] = $row["parent_id"];
		$dataThumb["user_id"] = $row["user_id"];
		$dataThumb["my"] = isset($user['id']) ? ($row["user_id"] == $user['id']) : false;
		if ($superAdmin) $dataThumb["my"] = true;
		$dataThumb["user_name"] = trim($row["first_name"] . " " . mb_substr($row["last_name"], 0, 1)) . ".";
		// if ($superAdmin) $dataThumb["user_name"] .= ' <span style="font-size: 12px; color: #ccc;>#' . $row["user_id"] . '</span>';
		$dataThumb["comment"] = $row["comment"];
		$dataThumb["likes"] = $row["likes"];
		$dataThumb["reply"] = $row["reply"];
		$dataThumb["datetime"] = date('d-m-Y H:i:s', $row["datetime"]);

		if ($type == 'like' or $type == 'unlike' or $subtype == 'edit') {
			$dataThumb['comments'] = array();
		} else {
			$dataThumb['comments'] = loadComments($row["coupon_id"], $row["comment_id"]);
		}

		$data[$count] = $dataThumb;
		$count++;
	}

	$db = null;

	return $data;
}