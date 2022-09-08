function adjustBlockPosition(block)
{
	let min_width = parseInt(block.css('min-width'));
	let max_width = parseInt(block.css('max-width'));

	let width = Math.min(Math.max(block.width(), min_width), max_width);

	let window_width = $(window).width();
	let margin = (window_width - width)/2 + 'px';

	block.css({'left': margin, 'right': margin});
}


function showPopup(id)
{
	let block = (typeof(id) === 'string') ? $(id) : id;
	block.add('#backgr').show();

	adjustBlockPosition(block);
}


function hidePopup(id)
{
	let block = (typeof(id) === 'string') ? $(id) : id;
	block.add('#backgr').hide();

	if (block.is('#nb-form')) resetCapthca();
}


function captchaCallback()
{
	let form = $('#nb-form');

	form.data('captcha-passed', 'ok');
	$('.errbox', form).html('').hide();
}


function resetCapthca()
{
	let form = $('#nb-form');

	form.data('captcha-passed', '');
	$('.errbox', form).html('').hide();

	grecaptcha.reset();
	console.log('captcha resetted');
}


function htmlSpecialChars(str)
{
	let map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};

	return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}


$(document).ready(function()
{
	$('.nb-add-new').on('click', function()
	{
		let form = $('#nb-form');

		form.data('id', 0);
		$('input[type!="submit"]', form).val('');

		showPopup(form);

		return false;
	});


	$('#nb-data').on('click', '.nb-edit', function()
	{
		let tr = $(this).closest('tr');
		let tds = $('> td', tr);

		let id = tr.data('id');
		let form = $('#nb-form');

		form.data('id', id);

		$('.nb-name',  form).val(tds.eq(0).text());
		$('.nb-dept',  form).val(tds.eq(1).text());
		$('.nb-phone', form).val(tds.eq(2).text());

		showPopup(form);

		return false;
	})
	.on('click', '.nb-del', function()
	{
		let id = $(this).closest('tr').data('id');
		let form = $('#nb-del-confirm');

		form.data('id', id);

		showPopup(form);

		return false;
	});


	$('.nb-close' ).on('click', function() { hidePopup('#nb-form'); return false; });
	$('.nb-del-no').on('click', function() { hidePopup('#nb-del-confirm'); });


	$('#nb-form').on('submit', function()
	{
		let form = $(this);
		let id = form.data('id');

		let name  = $('.nb-name',  form).val().trim();
		let dept  = $('.nb-dept',  form).val().trim();
		let phone = $('.nb-phone', form).val().trim();

		let errors = [];
		if (name == '') errors.push('Нужно указать имя');
		if (dept == '') errors.push('Нужно указать подразделение');
		if (!phone_regexp.test(phone)) errors.push('Неверный формат телефонного номера');

		if (form.data('captcha-passed') != 'ok') errors.push('Не пройдена проверка "Я не робот"');

		if (errors.length == 0)
		{
			$('input', form).prop('disabled', true);
			$('.nb-submit', form).val('Сохранение...');

			let gcr = $('#g-recaptcha-response').val();

			$.post('/api.php?action=write&id=' + id,
			{
				'name':  name,
				'dept':  dept,
				'phone': phone,
				'g-recaptcha-response': gcr
			},
			function(res)
			{
				if (res)
				{
					if (id)
					{
						if (res.id)
						{
							let tds = $('#nb-data tr[data-id="' + id + '"] td');
							tds.eq(0).html(htmlSpecialChars(name));
							tds.eq(1).html(htmlSpecialChars(dept));
							tds.eq(2).html(htmlSpecialChars(phone));

							hidePopup(form);
						}
						else
						{
							$('.errbox', form).html('Данная запись была удалена ранее').show();
							$('#nb-data tr[data-id="' + id + '"]').remove();
							if (!$('#nb-data tr').length) $('#nb-empty').show();
						}
					}
					else
					{
						let html = '<tr data-id="' + res.id + '">';
						html += '<td>' + name  + '</td>';
						html += '<td>' + dept  + '</td>';
						html += '<td>' + phone + '</td>';
						html += '<td class="actions align-center"><a href="#" class="nb-edit">&#9998;</a>&ensp;<a href="#" class="nb-del">&#128465;</a></td>';
						html += '</tr>';

						$('#nb-data').append(html);
						$('#nb-empty').hide();

						hidePopup(form);
					}
				}
				else $('.errbox', form).html('Получен пустой ответ сервера').show();

				$('input', form).prop('disabled', false);
				$('.nb-submit', form).val('Сохранить');
			}, 'json')
			.fail(function(jx)
			{
				$('.errbox', form).html(jx.responseText).show();

				$('input', form).prop('disabled', false);
				$('.nb-submit', form).val('Сохранить');
			});
		}
		else $('.errbox', form).html(errors.join('<br>')).show();

		return false;
	});


	$('#nb-form .fields input').on('keyup', function()
	{
		$('#nb-form .errbox').html('').hide();
	});


	$('.nb-del-yes').on('click', function()
	{
		let form = $('#nb-del-confirm');
		let id = form.data('id');

		$('input', form).prop('disabled', true);
		$('.nb-del-yes', form).val('Удаление...');

		$.get('/api.php', { action: 'del', id: id }, function(res)
		{
			if (res == '')
			{
				$('#nb-data tr[data-id="' + id + '"]').remove();
				if (!$('#nb-data tr').length) $('#nb-empty').show();

				hidePopup(form);
			}
			else $('.errbox', form).html(res).show();

			$('input', form).prop('disabled', false);
			$('.nb-del-yes', form).val('Удалить');
		})
		.fail(function(jx)
		{
			$('.errbox', form).html(jx.responseText).show();

			$('input', form).prop('disabled', false);
			$('.nb-del-yes', form).val('Удалить');
		});
	});


	$(window).on('resize', function()
	{
		$('.popup:visible').each(function()
		{
			adjustBlockPosition($(this));
		});
	});
});