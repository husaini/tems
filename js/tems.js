var SelectOption;
$(function() {
    SelectOption = function() {
        this.id = 0;
        this.ie = 0;
        this.f = 'init'
        this.callback = null;
        this.targetId = null;
        this.addEmpty = false;
        this.url = 'selectget.php';
        this.defaultOpt = '<option></option>';
        this.groupOptions = false;

        var self = this;

        this.get = function() {

            if(!self.targetId || $('#' + self.targetId).length == 0) {
                return;
            }
            var target = $('#'+self.targetId);
            var options = '';
            var data = {
                id: self.id,
                ie: self.ie,
                f: self.f
            };
            if(this.groupOptions === true) {
                data.group_options = 1;
            }
            $.getJSON(self.url,data, function(j) {
                if (self.addEmpty && typeof(self.addEmpty) == 'boolean') {
                    options +=  self.defaultOpt;
                }
                if (self.groupOptions === true) {
                    $.each(j, function(x,s) {
                        options +=  '<optgroup id="'+x+'" label="'+s.site+'">';
                        $.each(s.options, function(j,o) {
                            options += '<option value="' + o.optionValue + '">' + o.optionDisplay + '</option>';
                        });
                        options +=  '</optgroup>';
                    });
                } else {
                    for (var i = 0; i < j.length; i++) {
                        options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                    }
                }
                target.html(options);
                $('#'+this.targetId+' option:first').attr('selected', 'selected');
                if ($.isFunction(self.callback)) {
                    self.callback(j);
                }
            });
            return this;
        };
    };
});
