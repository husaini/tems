$(function() {
    $('#frmAsset').submit(function() {
        console.log($('.chk-any:checked').length);
        if($('.chk-any:checked').length > 0) {
            return true;
        }
        alert('Nothing to add. No asset selected');
        return false;
    });
    //wrap up the redraw function with our new shiz
    var dpFunc = $.datepicker._generateHTML; //record the original
    $.datepicker._generateHTML = function(inst){
        var thishtml = $( dpFunc.call($.datepicker, inst) ); //call the original

        thishtml = $('<div />').append(thishtml); //add a wrapper div for jQuery context

        //locate the button panel and add our button - with a custom css class.
        $('.ui-datepicker-buttonpane', thishtml).append(
            $('<button class="\
                ui-datepicker-clear ui-state-default ui-priority-primary ui-corner-all\
                "\>Clear</button>'
            ).click(function(){
                inst.input.attr('value', '');
                inst.input.datepicker('hide');
                var actualInput = inst.input.attr('id').replace('_date', '_date_db');
                $('#'+actualInput).val('');
            })
        );

        thishtml = thishtml.children(); //remove the wrapper div

        return thishtml; //assume okay to return a jQuery
    };
    $.datepicker._gotoToday = function(id) {
        var target = $(id);
        var inst = this._getInst(target[0]);
        if (this._get(inst, 'gotoCurrent') && inst.currentDay) {
                inst.selectedDay = inst.currentDay;
                inst.drawMonth = inst.selectedMonth = inst.currentMonth;
                inst.drawYear = inst.selectedYear = inst.currentYear;
        }
        else {
                var date = new Date();
                inst.selectedDay = date.getDate();
                inst.drawMonth = inst.selectedMonth = date.getMonth();
                inst.drawYear = inst.selectedYear = date.getFullYear();
                this._setDateDatepicker(target, date);
                this._selectDate(id, this._getDateDatepicker(target));
        }
        this._notifyChange(inst);
        this._adjustDate(target);
    }
    $('input.dp').attr('readonly', 'readonly').datepicker({
        altFormat: 'yy-mm-dd',
        autoSize: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'dd/mm/yy',
        onSelect: function(dateText, inst) {
            var actualInput = this.id.replace('_date', '_date_db');
            $(this).datepicker("option", "altField", "#"+actualInput);
        },
        showButtonPanel: true,
        closeText: 'Close'
    });
    var cache = {}, modelList = [];
    $( ".autocomplete" ).autocomplete({
        minLength: 1,
        source: function( request, response ) {
            var f = $(this.element).attr('rel');
            var manuId = $(this.element).attr('manuid');
            if(f=='modellist') {
                response(modelList);
            } else {
                $.getJSON('searchget.php?type=autocomplete&f='+f+'&ie='+manuId, request, function( data, status, xhr ) {
                    response(data);
                });
            }
        },
        select: function( event, ui ) {
            var modelInput = $(event.target).parent().next().find(':text');
            modelInput.attr('manuid', ui.item.id);
            $.getJSON('searchget.php?type=autocomplete&f=modellist&ie='+ui.item.id,function( data) {
                modelList = data;
            });
        }
    }).keyup(function(e) {
        if(e.keyCode != 13) {
            var modelInput = $(e.target).parent().next().find(':text');
            modelInput.removeAttr('manuid');
            var f = $(e.target).attr('rel');
            if(f == 'manulist') {
                modelList = [];
            }
        }
    });
    $('.chk-all').change(function() {
        $(this).parentsUntil('table').parent().find('.chk-any').attr('checked', this.checked);
    });

    $('th.site').click(function() {
        if($(this).hasClass('hidden')) {
            $(this).closest('table').find('tbody >').each(function() {
                $(this).show();
            });
            $(this).removeClass('hidden');
        } else {
            $(this).closest('table').find('tbody >').each(function() {
                $(this).hide();
            });
            $(this).addClass('hidden');
        }
        return false;
    });
});
