var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function($, undefined) {
    $(function() {
        "use strict";
        var $frmCreateVenue = $("#frmCreateVenue"),
                $frmUpdateVenue = $("#frmUpdateVenue"),
                $frmUpdateSector = $('#frmUpdateSector'),
                $dialogUpdate = $("#dialogUpdate"),
                $dialogDel = $("#dialogDelete"),
                $boxMap = $("#boxMap"),
                datagrid = ($.fn.datagrid !== undefined),
                validate = ($.fn.validate !== undefined),
                vOpts = {
            rules: {
                seat_number: {
                    required: function() {
                        if ($('#seats_count').val() != '')
                        {
                            var result = false;
                            $('.number-field').each(function(i, ele) {
                                if ($(ele).val() == '')
                                {
                                    result = true;
                                }
                            });
                            return result;
                        } else {
                            return false;
                        }
                    }
                }
            },
            messages: {
                number_of_seats: {
                    required: myLabel.seats_required
                },
                seat_number: {
                    required: myLabel.seat_numbers_required
                },
                seats_count: {
                    positiveNumber: myLabel.seat_count_greater_zero
                }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element.parent());
            },
            onkeyup: false,
            errorClass: "err",
            wrapper: "em",
            ignore: '',
            invalidHandler: function(event, validator) {
                $(".pj-multilang-wrap").each(function(index) {
                    if ($(this).attr('data-index') == myLabel.localeId)
                    {
                        $(this).css('display', 'block');
                    } else {
                        $(this).css('display', 'none');
                    }
                });
                $(".pj-form-langbar-item").each(function(index) {
                    if ($(this).attr('data-index') == myLabel.localeId)
                    {
                        $(this).addClass('pj-form-langbar-item-active');
                    } else {
                        $(this).removeClass('pj-form-langbar-item-active');
                    }
                });
            }
        };
        if ($frmCreateVenue.length > 0 || $frmUpdateVenue.length > 0) {
            $.validator.addMethod('positiveNumber',
                    function(value) {
                        return Number(value) > 0;
                    },
                    myLabel.seat_count_greater_zero);
        }


        function collisionDetect(o) {
            var i, pos, horizontalMatch, verticalMatch, collision = false;
            $("#mapHolder").children("span").each(function(i) {
                pos = getPositions(this);
                horizontalMatch = comparePositions([o.left, o.left + o.width], pos[0]);
                verticalMatch = comparePositions([o.top, o.top + o.height], pos[1]);
                if (horizontalMatch && verticalMatch) {
                    collision = true;
                    return false;
                }
            });
            if (collision) {
                return true;
            }
            return false;
        }
        function getPositions(box) {
            var $box = $(box);
            var pos = $box.position();
            var width = $box.width();
            var height = $box.height();
            return [[pos.left, pos.left + width], [pos.top, pos.top + height]];
        }

        function comparePositions(p1, p2) {
            var x1 = p1[0] < p2[0] ? p1 : p2;
            var x2 = p1[0] < p2[0] ? p2 : p1;
            return x1[1] > x2[0] || x1[0] === x2[0] ? true : false;
        }

        function updateElem(event, ui) {
            var $this = $(this),
                    rel = $this.attr("rel"),
                    $hidden = $("#" + rel),
                    val = $hidden.val().split("|");
            $hidden.val([val[0], parseInt($this.width(), 10), parseInt($this.height(), 10), ui.position.left, ui.position.top, $this.text(), val[6], val[7]].join("|"));
        }
        function getMax() {
            var tmp, index = 0;
            $("span.empty").each(function(i) {
                tmp = parseInt($(this).attr("rel").split("_")[1], 10);
                if (tmp > index) {
                    index = tmp;
                }
            });
            return index;
        }

        
       
        if ($frmUpdateVenue.length > 0) {      

            var offset = $("#map").offset(),
                    dragOpts = {
                containment: "parent",
                stop: function(event, ui) {
                    updateElem.apply(this, [event, ui]);
                }
            };
            $("span.empty").draggable(dragOpts).resizable({
                resize: function(e, ui) {
                    var height = $(this).height();
                    $(this).css("line-height", height + "px");
                },
                stop: function(e, ui) {
                    var height = $(this).height();
                    $(this).css("line-height", height + "px");
                    updateElem.apply(this, [e, ui]);
                }
            }).bind("click", function(e) {
                $dialogUpdate.data('rel', $(this).attr("rel")).dialog("open");
                $(this).siblings(".rect").removeClass("rect-selected").end().addClass("rect-selected");
            });



            $("#mapHolder").click(function(e) {
                var px = $('.bsMapHolder').scrollLeft();
                //console.log(e.pageY +'-'+ offset.top);
                var $this = $(this),
                        index = getMax(),
                        t = Math.ceil(e.pageY - offset.top - 30),
                        l = Math.ceil(e.pageX - offset.left - 8 + px),
                        w = 30,
                        h = 30,
                        o = {top: t, left: l, width: w, height: h};

                if (!collisionDetect(o)) {
                    index++;
                    $("<span>", {
                        css: {
                            "top": t + "px",
                            "left": l + "px",
                            "width": w + "px",
                            "height": h + "px",
                            "line-height": h + "px",
                            "position": "absolute"
                        },
                        html: '<span class="bsInnerRect" data-name="hidden_' + index + '">' + index + '</span>',
                        rel: "hidden_" + index,
                        title: index
                    }).addClass("rect empty new").draggable(dragOpts).resizable({
                        resize: function(e, ui) {
                            var height = $(this).height();
                            $(this).css("line-height", height + "px");
                        },
                        stop: function(e, ui) {
                            var height = $(this).height();
                            $(this).css("line-height", height + "px");
                            updateElem.apply(this, [e, ui]);
                        }
                    }).bind("click", function(e) {
                        $dialogUpdate.data('rel', $(this).attr("rel")).dialog("open");
                        $(this).siblings(".rect").removeClass("rect-selected").end().addClass("rect-selected");
                    }).appendTo($this);

                    $("<input>", {
                        type: "hidden",
                        name: "seats_new[]",
                        id: "hidden_" + index
                    }).val(['x', w, h, l, t, '', '', ''].join("|")).appendTo($("#hiddenHolder")); // set default value

					$("id").val(index);
                } else {
                    if (window.console && window.console.log) {
                    }
                }
            });

           

            if ($dialogUpdate.length > 0) {
                var seat_id = null;
                $dialogUpdate.dialog({
                    autoOpen: false,
                    resizable: false,
                    draggable: false,
                    modal: true,
                    width: 440,
                    open: function() {
                        var rel = $(this).data("rel"),
                                arr = $("#" + rel).val().split("|");
                        $("#ticket_type").val(arr[5]);
                        $("#seat_name").val(arr[6]);
                        $("#seat_seats").val(arr[7]);
                        $("#redeem_on").val(arr[8]);
                        $("#price").val(arr[9]);
                        seat_id = arr[0];
                    },
                    close: function() {
                        $("#seat_name, #seat_seats, #ticket_type, #redeem_on, #price").val("");
                    },
                    buttons: (function() {
                        var buttons = {};
                        buttons[tbApp.locale.button.save] = function() {
                            var rel = $(this).data("rel"),
                                    pName = $("#seat_name").val(),
                                    pType = $("#ticket_type").val(),
                                    pSeats = parseInt($("#seat_seats").val(), 10),
                                    pRedeem = $("#redeem_on").val(),
                                    pPrice = parseInt($("#price").val(), 10),
                                    pHidden = $("#" + rel, $frmUpdateVenue).val();

                            var err = 0;
                            if (pPrice > 0) {
                                $("#price").removeClass('error');
                            } else {
                                err = 1;
                                $("#price").addClass('error');
                            }
                            if (pSeats > 0)
                            {
                                $("#seat_seats").removeClass('error');
                            } else {
                                err = 1;
                                $("#seat_seats").addClass('error');
                            }
                            if (pName == "") {
                                err = 1;
                                $("#seat_name").addClass('error');
                            } else {
                                $("#seat_name").removeClass('error');
                            }
                            if (pType == "") {
                                err = 1;
                                $("#ticket_type").addClass('error');
                            } else {
                                $("#ticket_type").removeClass('error');
                            }
                            if (pRedeem == "") {
                                err = 1;
                                $("#redeem_on").addClass('error');
                            } else {
                                $("#redeem_on").removeClass('error');
                            }
                            if (err == 0) {
                                var a = pHidden.split("|");
                                var $rect_inner = $(".bsInnerRect[data-name='" + rel + "']", $frmUpdateVenue);
                              //  $rect_inner.text(pType + ' (' + pSeats + ')');
                                $rect_inner.text(pType);
                                $("#rbInner_" + rel).text(pName);
                                $("#" + rel).val([seat_id, a[1], a[2], a[3], a[4], pType, pName, pSeats, pRedeem, pPrice].join("|"));
                                $(this).dialog('close');
                            }
                        };
                        buttons[tbApp.locale.button.delete] = function() {
                            var rel = $(this).data('rel');
                            $("#" + rel, $("#hiddenHolder")).remove();
                            $(".rect-selected[rel='" + rel + "']", $("#mapHolder")).remove();

                            $(this).dialog('close');
                        };
                        buttons[tbApp.locale.button.cancel] = function() {
                            $dialogUpdate.dialog('close');
                        };

                        return buttons;
                    })()
                });
            }
        }

        
        var delay = (function() {
            var timer = 0;
            return function(callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();
    });
})(jQuery_1_8_2);