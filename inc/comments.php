<div id="comments" class="clearfix">
	<div class="outer">
		<div class="inner">
			<h6 class="title"><span id="comments-title">Günün Banko Kuponları</span>
				<!--(<span id="total-comment">25</span> Yorum)--> <a class="close-btn"></a>
			</h6>
			<div class="comment-list scrolldiv" id="comment-list"></div>

			<div class="form">
				<h5>Yorum Yaz</h5>
				<form action="comments.php" method="POST" class="ftCommentForm" id="ftCommentForm">
					<input name="type" type="hidden" value="save" />
					<input name="subtype" type="hidden" value="add" />
					<input name="coupon_id" type="hidden" value="0" />
					<input name="user_id" type="hidden" value="<?php echo $user['id'] ?>" />
					<input name="comment_id" type="hidden" value="0" />
					<input name="parent_id" type="hidden" value="0" />
					<div class="label-div">
						<div class="maxCharCounter">
							<textarea placeholder="Yorum yaz..." name="comment" cols="3" rows="3" maxlength="500" <?php if (!isset($user['id'])) echo ' disabled' ?>></textarea>
							<i class="maxchar"><i>500</i> Karakter</i>
						</div>
						<?php if (!isset($user['id'])) { ?>
							<div class="fb-login">
								<div>
									<h6>Yorum yapmak için giriş yapın</h6>
									<a href="<?php echo $loginUrl ?>" title="Facebook ile giriş yap" rel="nofollow" id="fbLoginBtn"></a>
								</div>
							</div>
						<?php } ?>
					</div>
					<?php if (isset($user['id'])) { ?>
						<a href="<?php echo $logoutUrl ?>" class="logout">ÇIKIŞ YAP</a>
					<?php } ?>

					<button class="submit-button" type="submit" <?php if (!isset($user['id'])) echo ' disabled' ?>>
						<span>GÖNDER</span>
					</button>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="comments_trans_bg"></div>


<script id="template" type="text/template7">
	{{#each comments}}
		<div class="comment" data-comment_id="{{comment_id}}" data-parent_id="{{parent_id}}" data-coupon_id="{{coupon_id}}" data-user_id="{{user_id}}">
			<img alt="{{user_name}}" src="<?php echo SITE_URL ?>/assets/images/comment_user.png" />
			<h4>{{user_name}}<?php if ($superAdmin) echo ' <i>#{{user_id}}</i>' ?></h4>
			<p>{{comment}}</p>
			<div class="clr"></div>
			<div class="btns">
				<div>
					<a class="like">Beğen (<span class="total-like">{{likes}}</span>)</a>
					<a class="reply">Cevapla (<span class="total-reply">{{reply}}</span>)</a>
					{{#if my}}<a class="edit">Düzenle</a>{{/if}}
					{{#if my}}<a class="delete">Sil</a>{{/if}}
					<?php if ($superAdmin) echo '{{#js_if "' . $user['id'] . ' !== this.user_id"}}<a class="banned"></a>{{/js_if}}' ?>

				</div>
			</div>
			<div class="clr"></div>
			<div class="form subform"></div>
			<div class="clr"></div>
			{{#if comments}}{{> "comments"}}{{/if}}
		</div>
	{{/each}}
</script>