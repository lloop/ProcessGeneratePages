$(document).ready(function(){

	/* GENERAL
	 ***********************************************************************/

//	var $list = $(".container > .inputfields");

	// remove scripts, because they've already been executed since we are manipulating the DOM below (WireTabs)
	// which would cause any scripts to get executed twice
//    $list.find("script").remove();


//    console.log($('.container > ul.Inputfields > li.Inputfield'));

	//WireTabs
    var $fieldEdit = $('.container > .Inputfields');
    // Eliminates the 'ProcessPageList' appearing twice
    $fieldEdit.find('script').remove();
    $fieldEdit.WireTabs({
        items: $(".tabs"),
        id: 'PageEditTabs',
        rememberTabs: 1
    });


	//Submit the right button when we are on the edit screen
//	$(document).keypress(function(e){f
//		edit = $('.WireTabs li:first a').hasClass('on');
//		if (e.which == 13 && edit) {
//			$('#batcher_search').trigger('click');
//		}
//	})

	/* EDIT
	 ***********************************************************************/

	/**
	 * Change select of actions
	 */

	/**
	 * Toggle all checkbox in th
	 */
	$('input.toggle_all').click(function(){
		if ($(this).prop('checked')) {
			$('.toggle').prop('checked', true);
		} else {
			$('.toggle').prop('checked', false);
		}
	});

	/**
	 * Setup fancybox for page edits
	 */
//	var h = $(window).height()-65;
//    var w = $(window).width() > 1150 ? 1150 : $(window).width()-100;
//
//	$('.batcher_edit').fancybox({
//		type : 'iframe',
//		frameWidth : w,
//		frameHeight : h
//	});

	/**
	 * Fix for MarkupAdminDataTable: Don't enable sorting on first column
	 */
	if ($.tablesorter != undefined) $.tablesorter.defaults.headers = {0:{sorter:false}};


	/* CREATE
	 ***********************************************************************/
//
//	$('table.batcher_create tbody').sortable();
//
//	/**
//	 *  Clone the last tr and set some values for the form elements
//	 */
//	var clonePageRow = function() {
//		$tr = $('table.batcher_create tr:last');
//		$clone = $tr.clone();
//		$clone.find('input[type=text]').attr('value','');
//		var template = $tr.find('option:selected').attr('value');
//		if (template) {
//			$clone.find('select option[value="'+template+'"]').attr('selected', 'selected');
//		}
//		$('table.batcher_create').append($clone);
//		$clone.find('input:first').focus();
//		return false;
//	}
//
//	$('.batcher_add').click(clonePageRow);
//	$(document).bind('keydown', 'ctrl+n', clonePageRow);
//
//	/**
//	 * Remove a tr
//	 */
//	 var removeTr = function() {
//	 	var $tr = $(this).closest('tr');
//		if ($tr.prev('tr').find('input').length) $tr.remove();
//		return false;
//	 };
//
//	if ($.isFunction($(document).on)) {
//	    $(document).on('click', '.remove_page', removeTr);
//	} else {
//	    $('.remove_page').live('click', removeTr);
//	}
//
//
//	/**
//	 * Modify checkboxes which are not checked to hidden fields, sending the value "0"
//	 */
//	$('#create_pages').click(function(){
//		//Set checkbox type to hidden if not checked to make sure a value gets send
//		$('input[type=checkbox]').each(function(){
//			$checkbox = $(this);
//			if (!$checkbox.prop('checked')) {
//				var name = $checkbox.attr('name');
//				$checkbox.replaceWith('<input type="hidden" name="'+name+'" value="0">');
//			}
//		});
//	});

    /* FIELDS CONFIG
     ***********************************************************************/

    /*
     * Fold out rows on field config tab
     * todo interferes with the showIf feature
     *
     */
    $('.field-content').hide();
    $('.ui-widget-gp.ui-icon-triangle-1-e').click(function(){
        $(this).toggleClass('ui-icon-triangle-1-e ui-icon-triangle-1-s');
        // needs to be this - parent - next - children
        $(this).parent().next().children().find('.field-content').toggle();
    });

    /*
     * Filter the table rows according to the template pulldown
     *
     * todo interferes with the showIf feature
     *
     */
//    var $pulldown = $('#field-filter'),
//        $table = $('table.gen-pages-fields');
//
//    $pulldown.change(function() {
//        // Split and concat the value of the pulldown
//        var pd_val = $pulldown.val()
//            .split(',')
//            .map(function(el){return 'tr span.' + el + 'fgroup';})
//            .join(',');
//
//        if(pd_val.indexOf('tr span.0fgroup') !== -1) {
//            console.log(pd_val);
//            $table.find('tr').show();
//        } else {
//            console.log(pd_val);
//            $table.find('tr').hide();
//            // Show all the table rows that have any of the pulldown value in the array
//            $table.find(pd_val).parent().parent().show();
//        }
//    });

    /*
     * Only submit the inputs that have been changed
     *
     * error : TemplateFile: This request was aborted because it appears to be forged.
     */
//    var inputs = $('.InputfieldForm input');
//
//    inputs.each(function() {
//        console.log('bingo');
//        $(this).data('original', this.value);
//    });
//
//    $('#fields_form').submit(function(){
//        console.log('bingo2');
//        inputs.each(function() {
//            if ($(this).data('original') === this.value) {
//                $(this).prop('disabled', true);
//            }
//        });
//    });

});