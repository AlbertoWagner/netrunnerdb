NRDB.data_loaded.add(function() {
	NRDB.data.sets({
		code : "alt"
	}).remove();

	NRDB.data.cards({
		set_code : "alt"
	}).remove();

	$(this).closest('tr').siblings().removeClass('active');
	$(this).closest('tr').addClass('active');
	for (var i = 0; i < Decklist.cards.length; i++) {
		var slot = Decklist.cards[i];
		NRDB.data.cards({
			code : slot.card_code
		}).update({
			indeck : parseInt(slot.qty, 10)
		});
	}
	update_deck();
});

$(function() {
	$('#decklist-social-icons>a').tooltip();
	$('#decklist-edit').on('click', edit_form);
	$('#decklist-delete').on('click', delete_form);
	$('#decklist-social-icon-like').on('click', send_like);
	$('#decklist-social-icon-favorite').on('click', send_favorite);
	$('#decklist-social-icon-comment').on('click', function() {
		$('#comment-form-text').trigger('focus');
	});

	var converter = new Markdown.Converter();
	$('#comment-form-text').on(
			'keyup',
			function() {
				$('#comment-form-preview').html(
						converter.makeHtml($('#comment-form-text').val()));
			});
	$('#btn-group-decklist').on({
		click : do_action_decklist
	}, 'button[id],a[id]');
	$('#menu-sort').on({
		change : function(event) {
			if ($(this).attr('id').match(/btn-sort-(\w+)/)) {
				DisplaySort = RegExp.$1;
				update_deck();
			}
		}
	}, 'a');

	$('#comment-form-text').textcomplete(
			[
					{
						match : /\B#([\-+\w]*)$/,
						search : function(term, callback) {
							callback(NRDB.data.cards({
								title : {
									likenocase : term
								}
							}).get());
						},
						template : function(value) {
							return value.title;
						},
						replace : function(value) {
							return '[' + value.title + ']('
									+ Routing.generate('netrunnerdb_netrunner_cards_zoom', {card_code:value.code})
									+ ')';
						},
						index : 1
					}, {
						match : /\B@([\-+\w]*)$/,
						search : function(term, callback) {
							var regexp = new RegExp('^' + term);
							callback($.grep(Commenters, function(commenter) {
								return regexp.test(commenter);
							}));
						},
						template : function(value) {
							return value;
						},
						replace : function(value) {
							return '`@' + value + '`';
						},
						index : 1
					} ]);

});

function edit_form() {
	$('#editModal').modal('show');
}

function delete_form() {
	$('#deleteModal').modal('show');
}

function do_action_decklist(event) {
	var action_id = $(this).attr('id');
	if (!action_id || !SelectedDeck)
		return;
	switch (action_id) {
	case 'btn-download-text':
		location.href = Routing.generate('decklist_export_text', {decklist_id:Decklist.id});
		break;
	case 'btn-download-octgn':
		location.href = Routing.generate('decklist_export_octgn', {decklist_id:Decklist.id});
		break;
	case 'btn-export-bbcode':
		export_bbcode();
		break;
	case 'btn-export-markdown':
		export_markdown();
		break;
	case 'btn-export-plaintext':
		export_plaintext();
		break;
	}
}

function send_like() {
	var obj = $(this);
	$.post(Routing.generate('decklist_like'), {
		id : Decklist.id
	}, function(data, textStatus, jqXHR) {
		obj.find('.num').text(data);
	});
}

function send_favorite() {
	var obj = $(this);
	$.post(Routing.generate('decklist_favorite'), {
		id : Decklist.id
	}, function(data, textStatus, jqXHR) {
		obj.find('.num').text(data);
		var title = obj.data('original-tooltip');
		obj.tooltip('destroy');
		obj.data('original-tooltip',
				title == "Add to favorites" ? "Remove from favorites"
						: "Add to favorites");
		obj.attr('title', obj.data('original-tooltip'));
		obj.tooltip('show');
	});
}
