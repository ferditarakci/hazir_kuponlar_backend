
/*
	HAZIR KUPONLAR - 25/10/2017
	Web Developer: Ferdi Tarakci
	Web Site: ferditarakci.com
*/

var htmlBody = ((navigator.userAgent.toLowerCase().indexOf('webkit') != -1) ? 'body' : 'html');

jQuery(function( $ ) {

	function bodyScrollWidth() {
		var ww1 = $(window).width();
		$('html, body').css('overflow', 'hidden');
		var ww2 = $(window).width();
		$('html, body').css('overflow', '');
		var sw = (ww2 - ww1);
		$('body').attr('data-sw', sw);
		$('#styleScrollWidth').remove();
		$('<style type="text/css" id="styleScrollWidth">\
			.html-lock body, .htmlHidden body, .html-lock .fixed-fixes, .htmlHidden .fixed-fixes {margin-right:' + sw + 'px !important;}\
			.htmlHidden #left-hkad .fixed-fixes {margin-right: 0 !important}\
			.htmlHidden #right-hkad .fixed-fixes {margin-left: -' + sw + 'px !important; margin-right: 0 !important;}\
		</style>').appendTo('head');
	}

	bodyScrollWidth();
	$(window).smartresize( bodyScrollWidth );


	$('#site-wrap > *, #header, #left-hkad, #right-hkad').filter(function() {
		var a = $(this).css('position');
		if (a == 'fixed') {
			$(this).wrapInner('<div class="fixed-fixes" />')
		}
	});

	$('.logo, .hkad, #left-hkad, #right-hkad, #footer-hkad, .fb-login').disableSelection();


	$('a[rel~=external]').each(function() {
		$(this).attr('target', '_blank');
	});



	$('#inner-wrap').prepend( $('#header') );






	function bets_resize() {

		$('.single .comments, .multi .comments, .textleft', '.bets-table').css('width', '');
		$('.bets-comments.multi', '.bets-table').css('height', '');
		$('.bets-body', '.bets-table').css('padding-bottom', '');
		$('.user, .comment', '.bets-table').removeClass('nowrap');

		var ww = ($(window).width() + parseInt($('body').attr('data-sw')));

		$('.bets-table').each(function() {

			$('.single .comments', this).css('width', $('.bets-comments.single .c1', this).actual('width'));
			$('.multi .comments', this).css('width', $('.bets-comments.multi .col', this).actual('width'));

			$('.user, .comment', this).addClass('nowrap');

			if (ww > 1074) {
				if ($(this).parent().is('.grid_b')) {
					var a = $(this).parent().prev('.grid_a').find('.bets-comments.multi').actual('height');
					var b = $(this).find('.bets-comments.multi').actual('height');

					if (a > b) {
						$(this).find('.bets-comments.multi').css('height', a);
						$(this).parent().prev('.grid_a').find('.bets-comments.multi').css('height', a);
					}

					else if (a < b) {
						$(this).find('.bets-comments.multi').css('height', b);
						$(this).parent().prev('.grid_a').find('.bets-comments.multi').css('height', b);
					}


					if ($(this).parent().hasClass('grid_b')) {
						var a = $(this).parent().prev('.grid_a').find('.bets-comments.multi .comments > div').length;
						var b = $(this).find('.bets-comments.multi .comments > div').length;

						if (a == 0 || b == 0) {
							$(this).parent().prev('.grid_a').find('.bets-comments.multi').hide();
							$(this).find('.bets-comments.multi').hide();
						}

						else if (a > b) {
							for (var s = 0; s < (a-b); s++) {
								$(this).parent().prev('.grid_a').find('.bets-comments.multi .comments > div').eq( (a-s)-1 ).hide();
							}
						}

						else if (a < b) {
							for (var s = 0; s < (b-a); s++) {
								$(this).find('.bets-comments.multi .comments > div').eq( (b-s)-1 ).hide();
							}
						}
					}

					var a1 = $(this).parent().prev('.grid_a').find('.bets-body').actual('height');
					var b1 = $(this).find('.bets-body').actual('height');

					var a2 = $(this).parent().prev('.grid_a').find('.bets-body .row').length;
					var b2 = $(this).find('.bets-body .row').length;

					if (a1 > b1) {
						$(this).find('.bets-body').css('padding-bottom', (a1 - (b2 * 32)) );
						//$(this).parent().prev('.grid_a').find('.bets-body').css('padding-bottom', (b1 - (b2 * 32)));
					}
					else if (a1 < b1) {
						//$(this).find('.bets-body').css('padding-bottom', (a1 - (b2 * 32)));
						$(this).parent().prev('.grid_a').find('.bets-body').css('padding-bottom', (b1 - (a2 * 32)));
					}
				}
			}

			else {
				if ($(this).find('.bets-comments.multi .comments > div').length) {
					$(this).find('.bets-comments.multi').show();
					$(this).find('.bets-comments.multi .comments > div').show();
				}
			}

		});
	}

	bets_resize();
	$(window).smartresize( bets_resize );







	createScroll();

	$(window).smartresize(function() {
		var ww = $(window).width() + parseInt($('body').attr('data-sw'));
		if (ww > 768) {
			if ($('#comment-list .tinyscroll').length) {
				updateScroll();
			}
			else {
				createScroll();
			}
		}
		else if (ww <= 768) {
			removeScroll();
		}
	});



	$('.open-comments, .close-btn, #comments_trans_bg').on(clickType, function(e) {

		var el = $(this);

		var div = el.parents('.grid_6').is('.grid_a') || $('#comments.open').hasClass('right'); //log(div)
		var direction = div ? 'right' : 'left';

		if (!el.hasClass('close-btn') && el.attr('id') != 'comments_trans_bg') {
			if (!div) $('#comments').removeClass('right'); else $('#comments').addClass('right');
		}

		var animation1 = {};
		var animation2 = {};
		animation1[direction] = '0';
		animation2[direction] = '-100%';

		if (!$('#comments').hasClass('open') && !el.hasClass('close-btn') && el.attr('id') != 'comments_trans_bg') {
			$('html').addClass('htmlHidden');
			$('#comments').addClass('open');
			$('#comments-title').html( el.parents('.bets-table').find('.bets-table-title').html() );

			$('#comment-list .overview').css('height', '').empty();

			loadComments({
				type: 'load',
				subtype: 'list',
				coupon_id: el.data('coupon_id'),
				parent_id: 0
			});

			var form = $('#ftCommentForm');
			form.find('[name="coupon_id"]').val(el.data('coupon_id'));
			// form.find('[name="comment_id"]').val(comment_id);
			// form.find('[name="parent_id"]').val(parent_id);
			// form.find('[name="comment"]').val('');

			navHeight();

			$('#comments_trans_bg').css('left', 0).animate({opacity: 1}, 400, function() {
				$('#comments').animate(animation1, 600);
			});
		}
		else {
			$('#comments').animate(animation2, 600, function() {
				$('#comments_trans_bg').animate({opacity: 0}, 400, function() {
					$('html').removeClass('htmlHidden');
					$('#comments').removeClass('open');
					$('#site-wrap, #comments, #comments_trans_bg').removeAttr('style');
					$('#site-wrap').css('min-height', parseInt($('#site-wrap').attr('data-minheight')));
				});
			});
		}

		e.stopPropagation();
		e.preventDefault();
	});





	$(document).on(clickType, '#comments .like', function(e) {

		var el = $(this);
		var parent = el.closest('.comment');
		var comment_id = parseInt(parent.data('comment_id'));
		var coupon_id = parseInt(parent.data('coupon_id'));

		var btns = parent.find('> .btns');

		var type = 'like';
		if (el.hasClass('active')) {
			type = 'unlike';
		}

		$.ajax({
			url: 'comments.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {
				ajax: 1,
				type: type,
				coupon_id: coupon_id,
				comment_id: comment_id
			}
		})
		.done(function (data, textStats, jqXHR) {
			if (data.errors) {
				swal({title: 'Dikkat!', html: data.message, type: 'warning', confirmButtonText: 'Tamam', confirmButtonColor: '#3d6d9e'});
			}
			else {log(data)
				var data = data.comments[0];
				btns.find('.total-like').text(data.likes);
				if (el.hasClass('active')) {
					el.removeClass('active');
				}
				else {
					el.addClass('active');
				}
			}
		});

		e.stopPropagation();
		e.preventDefault();
	});







	$(document).on(clickType, '#comments .reply, #comments .edit', function(e) {

		if (!site.login) {
			swal({title: 'Dikkat!', html: 'Üye girişi yapmadan cevap yazamazsınız!', type: 'warning', confirmButtonText: 'Tamam', confirmButtonColor: '#3d6d9e'});
			return false;
		}

		var el = $(this).closest('.comment');

		var comment_id = parseInt(el.data('comment_id'));
		var parent_id = parseInt(el.data('parent_id'));
		var user_id = parseInt(el.data('user_id'));
		var coupon_id = parseInt(el.data('coupon_id'));
		var subform = el.find('> .subform');

		var type = 'save';
		var subtype = 'reply';

		if ($(this).hasClass('edit')) {
			subtype = 'edit';
		}

		if(subtype == 'reply') {
			parent_id = comment_id;
			comment_id = 0;
		}


		if (subform.find('form').length) {
			if (confirm('Açık olan form kapatılsın mı?')) {
				//subform.find('form').remove();
				subform.find('[name="type"]').val(type);
				subform.find('[name="subtype"]').val(subtype);
				subform.find('[name="comment_id"]').val(comment_id);
				subform.find('[name="parent_id"]').val(parent_id);
				subform.find('[name="comment"]').val('');
				//autosize.destroy( subform.find('[name="comment"]') );
				//autosize( subform.find('[name="comment"]') );
				//subform.find('[name="comment"]').focus();
			}
			else {
				return false;
			}
		}

		subform.show();

		if (!subform.find('form').length) {
			var html = '<form action="comments.php" method="POST" class="ftCommentForm">';
				html += '<input name="type" type="hidden" value="' + type + '" />';
				html += '<input name="subtype" type="hidden" value="' + subtype + '" />';
				html += '<input name="coupon_id" type="hidden" value="' + coupon_id + '" />';
				html += '<input name="user_id" type="hidden" value="' + user_id + '" />';
				html += '<input name="comment_id" type="hidden" value="' + comment_id + '" />';
				html += '<input name="parent_id" type="hidden" value="' + parent_id + '" />';
				html += '<div class="label-wrapper">';
					html += '<div class="label-div">';
						html += '<div class="maxCharCounter">';
							html += '<textarea placeholder="Yanıt yaz..." name="comment" cols="3" rows="3" maxlength="500"></textarea>';
							html += '<i class="maxchar"><i>500</i> Karakter</i>';
						html += '</div>';
					html += '</div>';
					html += '<button class="submit-button" type="submit">';
						html += '<span>GÖNDER</span>';
					html += '</button>';
				html += '</div>';
			html += '</form>';

			subform.append( html );
		}

		// if (type == 'reply') {
		// 	subform.find('textarea').val(el.find('> h4').html() + ' ');
		// }

		var autosize_trigger = function( subform ) {
			var textarea = subform.find('textarea');
			autosize.destroy( textarea );
			autosize( textarea );

			textarea.on('autosize:resized', function() {
				setTimeout(function() {
					textarea.trigger('focus');
				}, 10);
			})
			.trigger('autosize:resized');
		}

		if (subtype == 'edit') {
			$.ajax({
				url: 'comments.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					ajax: 1,
					type: 'load',
					subtype: 'edit',
					user_id: user_id,
					coupon_id: coupon_id,
					comment_id: comment_id
				}
			})
			.done(function (data, textStats, jqXHR) {
				var data = data.comments[0];
				// log(data, data.comment)
				var coupon_id = data.coupon_id;
				var user_id = data.user_id;
				var comment_id = data.comment_id;
				var parent_id = data.parent_id;
				var comment = data.comment.replace(/<br\s*[\/]?>/gi, "\n ");
				subform.find('[name="coupon_id"]').val(coupon_id);
				subform.find('[name="user_id"]').val(user_id);
				subform.find('[name="comment_id"]').val(comment_id);
				subform.find('[name="parent_id"]').val(parent_id);
				subform.find('[name="comment"]').val(comment);

				autosize_trigger( subform );
			});
		}
		else {
			autosize_trigger( subform );
		}

		updateScroll('relative');
		e.stopPropagation();
		e.preventDefault();
	});







	$(document).on(clickType, '#comments .delete', function(e) {
		var el = $(this).closest('.comment');
		var user_id = parseInt(el.data('user_id'));
		var comment_id = parseInt(el.data('comment_id'));
		var coupon_id = parseInt(el.data('coupon_id'));

		if (confirm('Bu yorumu silmek istediğinizden emin misiniz?')) {
			$.ajax({
				url: 'comments.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					ajax: 1,
					type: 'delete',
					user_id: user_id,
					coupon_id: coupon_id,
					comment_id: comment_id
				}
			})
			.done(function (data, textStats, jqXHR) {
				if (data.status) {
					el.slideUp(800, function() {
						$(this).remove();
						updateScroll('relative');
					});
				}
				else {
					swal({title: 'Hata!', html: '<b>Mesajınız silinemedi!</b>', type: 'error', confirmButtonText: 'Tamam', confirmButtonColor: '#3d6d9e'});
				}
			});
		}

		e.stopPropagation();
		e.preventDefault();
	});





	$(document).on(clickType, '#comments .banned', function(e) {
		var el = $(this).closest('.comment');
		var user_id = parseInt(el.data('user_id'));

		if (confirm('Bu kullanıcıyı engellemek istediğinizden emin misiniz?')) {
			$.ajax({
				url: 'comments.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: {
					ajax: 1,
					type: 'banned',
					user_id: user_id
				}
			})
			.done(function (data, textStats, jqXHR) {
				$('#comments .comment[data-user_id="'+ user_id +'"]').slideUp(800, function() {
					$(this).remove();
					swal({title: 'Dikkat!', html: '#' + user_id + ' nolu kullanıcı engelledi.', type: 'success', confirmButtonText: 'Tamam', confirmButtonColor: '#3d6d9e'});
					updateScroll('relative');
				});
			});
		}

		e.stopPropagation();
		e.preventDefault();
	});





	$(window).load(function() {
		var hash = window.location.hash;
		if (hash.indexOf('banned=1') > -1) {
			window.location.hash = '#';
			swal({title: 'Dikkat!', html: '<b>Üyeliğiniz engellenmiştir!</b>', type: 'warning', confirmButtonText: 'Tamam', confirmButtonColor: '#3d6d9e'});
		}
	});





	$(document).on('submit', 'form.ftCommentForm', function(e) {
		e.preventDefault();

		var el = $(this);

		$(':submit', el).prop('disabled', true);

		setTimeout(function() {
			$(':submit', el).prop('disabled', false);
			el.find('.ftAlert').remove();
		}, 3000);

		$('textarea', el).parents('.label-div').removeClass('error');

		var type = $('[name="type"]', el).val();
		var subtype = $('[name="subtype"]', el).val();

		var _formData = el.serialize().replace(/%0D%0A/g, '<br>') + '&ajax=1';

		$.ajax({
			type: el.attr('method'),
			url: el.attr('action'),
			data: _formData,
			dataType: 'json',
			cache: false,
			beforeSend: function () {
				el.ftAlert({
					close: true,
					text: 'İşlem yapılıyor, Lütfen bekleyin.',
					cssClass: 'loading'
				});
			},
			error: function (jqXHR, textStatus, errorThrown) {
				el.ftAlert({
					close: true,
					text: 'Bir hata oluştu.',
					cssClass: 'error'
				});
			},
			success: function (data, textStatus, jqXHR) {

				if (!data.status) {
					if (data.errors == undefined) {
						$('.required').each(function(i, item) {
							if ($(this).val() == '') {
								$(this).parents('.label-div').addClass('error');
							}
						});
					}
					else {
						$.each(data.errors, function(i, item) {
							$('[name="' + i + '"]', el).parents('.label-div').addClass('error');
						});
					}

					el.ftAlert({
						close: true,
						text: data.message,
						cssClass: 'error'
					});
				}
				else if (data.status) {
					
					var cData = data.comments[0];
					var comment_id = cData.comment_id;
					var parent_id = cData.parent_id;
					var comment = cData.comment;

					if (type == 'save') {
						el.closest('.comment').find('p').html(data.comment);
						el.parent('.subform').hide().empty();

						if (subtype == 'add' || subtype == 'reply') {

							var template = '{{> "comments"}}'
							Template7.registerPartial(
								'comments',
								$('#template').html()
							);

							// var template = $('#template').html();
							var compiledTemplate = Template7.compile(template);
							var html = compiledTemplate(data);

							if (subtype == 'reply') {
								var comment = $('#comment-list div[data-comment_id="' + parent_id + '"]');
								comment.append(html);
								comment.find('> .btns .total-reply').html( data.parent_total_reply );
							}
							else {
								if (html != '' && $('#comment-list .no-comment').length) {
									$('#comment-list .overview').css('height', '').empty();
								}
								$('#comment-list .overview').prepend(html);
							}

							el.find('textarea').val('').trigger('keyup');

						}

						else if (subtype == 'edit') {

							var editComment = $('#comment-list div[data-comment_id="' + comment_id + '"]');
							editComment.find('> p').html( comment );
							el.parent('.subform').empty();

						}

						updateScroll('relative');

						// if (subtype == 'reply') {
						// 	if (parseInt(el.find('[name="parent_id"]').val()) == 0) {
						// 		el.parents('.comment').append(html);
						// 	}
						// 	else if (parseInt(el.find('[name="parent_id"]').val()) > 0) {
						// 		el.parents('.comment').parent('.comment').append(html);
						// 	}
						// 	el.parent('.subform').hide().empty();
						// }

					}

					el.ftAlert({
						close: true,
						text: data.message,
						cssClass: 'success'
					});

				}
			}
		});
	});








	var focusTimer;
	$(document).on('focus', '#comments .subform textarea', function() {
		if ($.browser.mobile) {
			clearInterval(focusTimer);
			$('#comments .inner').css('padding-bottom', '10px');
			$('#comments .inner > .form').hide();
		}
	})
	.on('blur', '#comments .subform textarea', function() {
		if ($.browser.mobile) {
			focusTimer = setInterval(function() {
				$('#comments .inner').css('padding-bottom', '');
				$('#comments .inner > .form').show();
				updateScroll('relative');
			}, 400);
		}
	});



	$(document).on('keyup', '.maxCharCounter textarea[maxlength]', function() {
		var val = $(this).val();
		var maxVal = parseInt( $(this).attr('maxlength') );
		if (val.length > maxVal) {
			$(this).val( val.substring(0, maxVal) );
			return false;
		}
		$(this).siblings('.maxchar').find('i').text((maxVal - val.length));
	});

	$('.maxCharCounter textarea[maxlength]').trigger('keyup');







	$('#footer-hkad').length && $('html').addClass('fpb');

	function hkadResize() {
		var ww = $(window).width();
		var container = 1070;
		if (ww <= 1280) container = 1000;
		var sw = parseInt($('body').attr('data-sw'));

		var a = (((ww - container) / 2) - 148);

		$('#left-hkad').css('left', a);
		$('#right-hkad').css('right', a);
	}


	hkadResize();
	$(window).smartresize( hkadResize );






	function fixedAds() {
		var sTop = $(window).scrollTop();
		var wh = $(window).height();

		var a = 5;
		var b = (660 - wh);
		//console.log(wh, b, sTop)

		if (b > sTop) a = -sTop;
		if (b < sTop) a = -b;
		if (wh > 660) a = 5;

		$('#left-hkad, #right-hkad').css('top', a);
		$('#site-wrap').css('min-height', '');
	}

	$(window).on('scroll resize', fixedAds);
	//$(window).load(function() { $(window).trigger('resize') });












	function fixedHeader() {
		var ww = ($(window).width() + parseInt($('body').attr('data-sw')));
		var wh = $(window).height();
		var fh = $('#footer').height();
		var sTop = $(window).scrollTop();
		var hh = $('#header').height();
		var htop = $('#header .res-bar').height();

		if (ww >= 1074) {
			$('#header').css('top', '');
			if (sTop > 0) {
				$('html').addClass('fixed');
			}
			else {
				$('html').removeClass('fixed');
			}
			$('html').removeClass('mobile-fixed-nav');
		}
		else {
			$('html').addClass('fixed mobile-fixed-nav');
		}

		var swh = $('#site-wrap').innerHeight();

		$('#site-wrap').css('min-height', wh);

		var ftkad = $('#footer-tkad').length;
		var ffix = $('#footer').hasClass('bottom-fix');

		if ((wh - fh) > swh && ffix) {
			$('#footer').addClass('bottom-fix');
		}
		else if ((wh - fh) > (swh - (ftkad ? 120 : (ftkad ? 0 : 60))) && !ffix) {
			$('#footer').addClass('bottom-fix');
		}
		else {
			$('#footer').removeClass('bottom-fix');
		}

		$('.mobile-fixed-nav').length && $('#footer').removeClass('bottom-fix');
	}

	fixedHeader();
	$(window).on('scroll resize', fixedHeader);














	if (!navigator.userAgent.match(/(iP(ad|od|hone))/i))
		$(document).on(clickType, 'a[href=#]', false);

	$('a[href=#]').doubleTapToGo();






	if ($('.pagination').length) {

		var paginationSelect = $('<select />').appendTo('.pagination');
		$('.pagination a').each(function(a, b) {
			var opt = $('<option />', {
				value: $(this).attr('href'),
				text: 'Sayfa ' + $(this).text()
			})
			.appendTo( paginationSelect );
			$(this).hasClass('selected') && opt.prop('selected', true);
		});

		paginationSelect.on('change', function() {
			window.location = $(this).val();
		});

	}







	$('#inner-wrap').append('<div id="res-nav"></div>');
	$('#inner-wrap').append('<div id="trans_bg"></div>');
	$('#navigation').wrapInner('<div class="navigation-inner" />');
	$('#navigation .socials').append( $('#footer .socials > div').clone() );
	$('#res-nav').append( $('#navigation') );

	function navHeight() {
		$('#site-wrap, #res-nav, #navigation, #comments').css('height', '');

		var siteWrapMinHeight = parseInt($('#site-wrap').css('min-height'));
		$('#site-wrap').attr('data-minheight', siteWrapMinHeight); $('#site-wrap').css('min-height', '');

		if ($('#res-nav, #site-fav, #comments').hasClass('open')) {
			//var windowHeight = $(window).height();
			var siteWrapHeight = $('#site-wrap').actual('height');
			//if ($('#footer').hasClass('bottom-fix')) siteWrapHeight = siteWrapHeight - parseInt($('#footer').actual('height'));

			//if (siteWrapMinHeight > siteWrapHeight) siteWrapHeight = siteWrapMinHeight;

			$('#site-wrap').height( siteWrapHeight );
			//$('#res-nav, #navigation').height( windowHeight );
		}

		$('#site-wrap').css('min-height', siteWrapMinHeight);
	}

	function navShowHide( e ) {
		if (!$('#res-nav').hasClass('open')) {

			$('html').addClass('htmlHidden');
			$('#res-nav, #trans_bg, #nav-button').addClass('open');

			navHeight();

			$('#trans_bg').css('left', 0).animate({ opacity: 1 }, 400, function() {
				$('#res-nav').animate({ left: 0 }, 600);
			});
		}
		else {

			$('#res-nav').animate({ left: '-100%' }, 600, function() {
				$('#trans_bg').animate({ opacity: 0 }, 400, function() {
					$('html').removeClass('htmlHidden');
					$('#res-nav, #trans_bg, #nav-button').removeClass('open');
					$('#site-wrap, #res-nav, #navigation, #trans_bg').removeAttr('style');
				});
			});
		}

		e.stopPropagation();
		e.preventDefault();
	}

	$(document).on(clickType, '#nav-button, #res-nav .close-btn, #trans_bg', navShowHide);

	$(window).smartresize(function() {
		navHeight();
		var ww = $(window).width();
		if ((ww + parseInt($('body').attr('data-sw'))) > 1074) {
			$('#nav-button.open').trigger( clickType );
		}
	});



	$(document).on('submit', 'form.ftAjaxForm', function( e ) {
		e.preventDefault();

		var el = $(this);

		$(':submit', el).prop('disabled', true);

		setTimeout(function() {
			$(':submit', el).prop('disabled', false);
		}, 3000);

		$('input, select, textarea', el).parents('label, .label-div').removeClass('error');
		var _formData = el.serialize().replace(/%0D%0A/g, '<br> ') + '&ajax=1';

		$.ajax({
			type: el.attr('method'),
			url: el.attr('action'),
			data: _formData,
			dataType: 'json',
			cache: false,
			beforeSend: function () {
				el.ftAlert({
					close: true,
					text: 'İşlem yapılıyor, Lütfen bekleyin.',
					cssClass: 'loading'
				});
			},
			error: function (jqXHR, textStatus, errorThrown) {
				el.ftAlert({
					close: true,
					text: 'Bir hata oluştu.',
					cssClass: 'error'
				});
			},
			success: function (data, textStatus, jqXHR) {

				if (!data.status) {
					if (data.errors == undefined) {
						$('.required').each(function(i, item) {
							if ($(this).val() == '') {
								$(this).parents('label, .label-div').addClass('error');
							}
						});
					}
					else {
						$.each(data.errors, function(i, item) {
							$('[name="' + i + '"]', el).parents('label, .label-div').addClass('error');
						});
					}

					el.ftAlert({
						close: true,
						text: data.message,
						cssClass: 'error'
					});
				}
				else if (data.status) {

					el.ftAlert({
						close: true,
						text: data.message,
						cssClass: 'success'
					});

					var redirect_url = $('[name="redirect_url"]', el);

					if (redirect_url.length == 1) {
						setTimeout(function() {
							window.location = redirect_url.val();
						}, 2000);
					}

					setTimeout(function() {
						if (redirect_url.length == 0) location.reload();
					}, 2000);
				}
			}
		});
	});

});




function loadComments(params) {
	// parent_id = parent_id || 0;

	// var params = {
	// 	type: 'load',
	// 	coupon_id: coupon_id,
	// 	parent_id: parent_id
	// };

	params['ajax'] = 1;

	$.ajax({
		url: 'comments.php',
		type: 'POST',
		dataType: 'json',
		cache: false,
		data: params
	})
	.done(function (data, textStats, jqXHR) {
		// var coupon_id = data['coupon_id'];
		// var user_id = data['user_id'];
		// var comment_id = data['comment_id'];
		// var parent_id = data['parent_id'];
		// var comment = data['comment'];
		// subform.find('[name="coupon_id"]').val(coupon_id);
		// subform.find('[name="user_id"]').val(user_id);
		// subform.find('[name="comment_id"]').val(comment_id);
		// subform.find('[name="parent_id"]').val(parent_id);
		// subform.find('[name="comment"]').val(comment);

		// autosize_trigger( subform );

		//log(data)

		// Simple template with just a partial
		var template = '{{> "comments"}}'

		// Register partial
		Template7.registerPartial(
			'comments',
			$('#template').html()
		);

		// var template = $('#template').html();
		var compiledTemplate = Template7.compile(template);
		var html = compiledTemplate(data);

		if ($('#comment-list .overview').length) {
			$('#comment-list .overview').append(html);

			if (!$('#comment-list .comment').length) {
				$('#comment-list .overview').css('height', '100%').html('<div class="no-comment"><div>Bu kupona ilk yorumu sen yap!</div></div>');
			}
		}
		else {
			$('#comment-list').append(html);

			if (!$('#comment-list .comment').length) {
				$('#comment-list').css('height', '100%').html('<div class="no-comment"><div>Bu kupona ilk yorumu sen yap!</div></div>');
			}
		}
	
		updateScroll('relative');
	});
}





function createScroll() {
	removeScroll();
	$('.scrolldiv').each(function() {
		var h = $(this).data('height');
		var w = $(window).width();
		$(this).wrapInner('<div class="tinyscroll" />');
		$('.tinyscroll', this).wrapInner('<div class="viewport" />');
		$('.viewport', this).wrapInner('<div class="overview" />');
		if (h == 'undefined') $('.viewport', this).height( h );
		if (w <= 840) $('.viewport', this).css('height', '');
		$('.tinyscroll', this).prepend('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>');
		this.$scroll = $('.tinyscroll', this).tinyscrollbar();
	});
}


function updateScroll(s) {
	$('.scrolldiv').each(function() {
		if ($('.tinyscroll', this).length) this.$scroll.data('plugin_tinyscrollbar').update((s || ''));
	});
}


function removeScroll() {
	$('.scrolldiv').each(function() {
		if ($('.tinyscroll', this).length) {
			var o = $(this).find('.overview').html();
			$(this).removeData('plugin_tinyscrollbar');
			$(this).html( o );
		}
	});
}




(function( $ ) {
	$.fn.disableSelection = function() {
		return this
			.addClass('noSelect')
			.attr('unselectable', 'on')
			.on('selectstart', false);
	};
})(jQuery);



function _websiteTrigger() {
	$(window).trigger('resize');
}

jQuery(function( $ ) {
	_websiteTrigger();
	$(window).load( _websiteTrigger );

	// if ($.browser.mobile && $('#fbLoginBtn').length) {
	// 	$('#fbLoginBtn').attr('href', $('#fbLoginBtn').attr('href').replace('https://www.facebook.com/', 'fb://')).removeAttr('target');
	// }

});