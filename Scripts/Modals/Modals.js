
class Modals extends BaseClass {
    constructor(PostDirectory) {
        super(PostDirectory);
    }

    ShowModal(params) {

        $('.loader').fadeOut();
        if (params.hasOwnProperty('style'))
            $(".modal-dialog").css(params.style);
        $("#mainModal").modal(params);


    }


    HideModal() {
        $("#mainModal").modal('hide');
    }

    ShowDialog(params) {
        $('.overlay').fadeIn();
        $('.main-dialog').fadeIn("slow", function () {
            $('.loader').fadeOut(500);
        });
    }



    HideDialog(params) {
        $('.overlay,.loader').fadeOut();
        $('.main-dialog').fadeOut(600);
        $("#dialog-confirm").show();
    }



    SetModal(params) {
        var dataString = [];
        var $form = $('#mainModalForm');
        $form.empty();
        $('#mainModal-title').text(params.title);
        $form.attr('data-reload', params.reload);
        var $actionButton = $('#mainModal-action');
        var $closeActionButton = $('#mainModalClose');
        if(params.hasOwnProperty('closeText')){
            $closeActionButton.text(params.closeText);
        }
        if (params.actionText === false) {
            $actionButton.hide();
            $actionButton.text(params.actionText);
        } else {
            $actionButton.show();
            if (params.trigger !== false)
                $actionButton.attr('data-trigger', params.trigger);
            if (params.type !== false)
                $actionButton.attr('data-type', params.type);
            $actionButton.text(params.actionText);
        }


        if (!params.hasOwnProperty('htmlFillOnly')) {
            if (params.ajax !== false)
                $form.addClass('ajaxForm');
            if (params.url !== false)
                $form.attr('data-url', params.url);


            if (params.closeTrigger !== false) {
                $closeActionButton.attr('data-trigger', params.closeTrigger);
                if (params.additionalData !== false) {
                    if (params.hasOwnProperty('additionalData'))
                        $closeActionButton.attr('data-target', params.additionalData.target);
                }
            }


        } else {
            if (!params.hasOwnProperty('impose'))
                params.impose = {};

            if (params.format === 'split') {
                var i = 0;
                dataString.push('<div class="row">');
                $.each(params.htmlFillOnly, function (k, v) {
                    if (($.inArray(k.toLowerCase(), params.htmlFillOnly.exclusions) === -1) === (k !== 'exclusions')) {
                        v = v !== null ? v : '--';
                        if (i % 2 === 0) {
                            dataString.push('<div class="col-xs-6"><p><b>' + k + '</b>: ' + v + '</p>');
                        } else {
                            dataString.push('<div class="col-xs-6"><p><b>' + k + '</b>: ' + v + '</p>');
                        }
                        i++;
                    }
                });
                dataString.push('<div class="clearfix"></div></div>');
            } else if (params.format === 'standard') {
                $.each(params.htmlFillOnly, function (k, v) {
                    if (($.inArray(k.toLowerCase(), params.htmlFillOnly.exclusions) === -1) === (k !== 'exclusions')) {
                        dataString.push('<p><b>' + k + '</b></p><p> ' + v + '</p>');
                    }
                });
            } else if (params.format === 'model') {
                var model = params.model;
                $.each(params.htmlFillOnly, function (k, v) {
                    if (String(v).indexOf("/Date(") >= 0)
                        v = moment(v).format('MM/DD/YY  h:mm a');
                    if (($.inArray(k.toLowerCase(), params.htmlFillOnly.exclusions) === -1) === (k !== 'exclusions')) {
                        var rep = new RegExp(k, 'g');
                        model = model.replace(rep, v);
                    }
                });
                dataString.push(model);
            } else if (params.format === 'table') {
                var i = 0;
                dataString.push('<table class="table table-bordered tbl-left-exception"><tbody>');
                var row = '';
                var ready;
                $.each(params.htmlFillOnly, function (k, v) {
                    if (($.inArray(k.toLowerCase(), params.htmlFillOnly.exclusions) == -1) == (k !== 'exclusions')) {
                        v = v !== null ? v : '--';
                        k = params.impose.hasOwnProperty(k) ? params.impose[k] : k;
                        if (String(v).indexOf("/Date(") >= 0) {
                            if (moment(v).year() !== 1) {
                                v = moment(v).format('MM/DD/YY h:mm a');
                            } else {
                                v = '--';
                            }
                        }
                        if (i % 2 === 0) {
                            ready = false;
                            row += '<tr><td style="text-align:right!important"><b>' + k + '</b></td><td>' + v + '</td>';
                        } else {
                            ready = true;
                            row += '<td style="text-align:right!important"><b>' + k + '</b></td><td>' + v + '</td></tr>';
                        }
                        if (ready) {

                            dataString.push(row);
                            row = "";
                        }

                        i++;
                    }
                });
                dataString.push('</tbody></table>');
            }

        }
        if (!params.hasOwnProperty('returnData')) {
            $form.append(dataString.join(''));
        } else {
            return dataString.join('');
        }

    }



    BuildMultiPageModelWindow(params) {
        let multiViewPopUp = true;
        var headers = ['<ul class="nav nav-tabs" id="mtc" role="tablist">'];
        var content = [];
        $.each(params.content, function (key, value) {
            var name = 'TAB_' + Core.GenerateKey(8);
            var isFirst = content.length == 0;
            var activeClass = isFirst ? ' show active' : '';

            headers.push('<li class="nav-item multi-modal-nav-tab" data-form="' + value.isForm + '"><a class="nav-link' + activeClass + '" id="' + name + '-tab" data-toggle="tab" href="#' + name + '" role="tab" aria-controls="' + name + '" aria-selected="true">' + value.title + '</a></li>');


            if (value.isForm) {

                content.push('<div class="tab-pane fade' + activeClass + '" id="' + name + '" role="tabpanel" aria-labelledby="' + name + '-tab">' + value.data + '</div>');

            } else {
                content.push('<div class="tab-pane fade' + activeClass + '" id="' + name + '" role="tabpanel" aria-labelledby="' + name + '-tab">' + value.data + '</div>');

            }

        });
        headers.push('</ul>');
        var $Modal = $('#mainModalForm');
        $Modal.empty();
        $Modal.append(headers.join(''));
        $Modal.append('<div class="tab-content">' + content.join('') + '</div>');
        //$('#mtcContent').append(content.join(''));


    }


    BuildForm(params) {
        var dataString = [];
        let optionName;
        var $form = $('#' + params.formID);
        $form.empty();
        $.each(params.data, function (key, value) {
            var referenceInputId = value[3] === false ? '' : value[3];
            var value_zero = value[0];
            var value_two = value[2];
            if (value[0] !== undefined) {
                try {
                    value_zero = value[0].indexOf(':') > -1 ? $(params.elem).attr(value[0].split(':')[1]) : value[0];
                } catch (er) {
                }
            }
            if (value[2] !== undefined) {
                try {
                    value_two = value[2].indexOf(':') > -1 ? $(params.elem).attr(value[2].split(':')[1]) : value[2];
                } catch (er) {
                }
            }
            switch (value[1]) {
                case 'select':
                    var id = Core.GenerateKey(7);
                    var optionBuilder = [];

                    $.each((value[4]), function (k, v) {
                        var skip = false;
                        optionName = value.length >= 5 ? value[5][k] : v;
                        if (!skip) {
                            if (value_two !== v) {
                                optionBuilder.push('<option value="' + v + '">' + Core.ReplaceUnderscores(optionName) + '</option>');
                            } else {
                                optionBuilder.push('<option value="' + v + '" selected="selected">' + Core.ReplaceUnderscores(optionName) + '</option>');
                            }
                        }
                    });
                    dataString.push('<p><b>' + value[0] + '</b></p><select class="form-control" id="' + id + '" data-refId="' + referenceInputId + '"  name="' + key + '" ><option disabled selected value> -- select an option -- </option>' + optionBuilder.join("") + '</select>');
                    break;
                case 'multiSelect':
                    var id = Core.GenerateKey(7);
                    var optionBuilder = [];

                    $.each((value[4]), function (k, v) {
                        var skip = false;
                        optionName = value.length >= 5 ? value[5][k] : v;
                        if (!skip) {
                            if (value_two !== v) {
                                optionBuilder.push('<option value="' + v + '">' + Core.ReplaceUnderscores(optionName) + '</option>');
                            } else {
                                optionBuilder.push('<option value="' + v + '" selected="selected">' + Core.ReplaceUnderscores(optionName) + '</option>');
                            }
                        }
                    });
                    dataString.push('<p><b>' + value[0] + '</b></p><select class="form-control" id="' + id + '" data-refId="' + referenceInputId + '"  name="' + key + '"  multiple size="' + value[4].length + '"><option disabled selected value> -- select an option -- </option>' + optionBuilder.join("") + '</select>');
                    break;
                case 'hidden':
                    dataString.push('<input class="form-control" type="' + value[1] + '" name="' + key + '" value="' + value_two + '">');
                    break;
                case 'radio':
                    dataString.push('<div class="radio-options" data-selected="false"><p><b>' + value[0] + '</b></p>');
                    $.each((value[4]), function (k, v) {
                        var id = Core.GenerateKey(7);
                        optionName = value.length >= 5 ? value[5][k] : v;
                        dataString.push('<input type="radio" name="' + key + '" id="' + id + '" value="' + v + '"><label for="' + id + '" >' + Core.ReplaceUnderscores(optionName) + '</label>');
                    });
                    dataString.push('</div>');
                    break;
                case 'textarea':
                    dataString.push('<p><b>' + value_zero + '</b></p><textarea class="form-control counted" data-max="1000" type="' + value[1] + '" name="' + key + '">' + value_two + '</textarea><small>Characters remaining: <span id="countedText">1000</span></small>');
                    break;
                case 'display-text':
                    dataString.push('<p><b>' + value_zero + '</b> ' + value_two + '</p>');
                    break;
                case 'text':
                    dataString.push('<p><b>' + value_zero + '</b></p><input class="form-control" type="' + value[1] + '" name="' + key + '" data-refId="' + referenceInputId + '" value="' + value_two + '">');
                    break;
                case 'html':
                    dataString.push(value[0]);
                    break;
                default:
                    dataString.push('<p><b>' + value_zero + '</b></p><input class="form-control" type="' + value[1] + '" name="' + key + '" data-refId="' + referenceInputId + '" value="' + value_two + '">');
                    break;
            }
        });

        if (!params.hasOwnProperty('returnData')) {
            $form.append(dataString.join(''));
        } else {
            return dataString.join('');
        }
    }

    ToggleVerificationMode(params){

        let isUnique = true;
        if(params.CheckUnique){
            isUnique = Core.ajaxRequest({
                URL:'VerifyUniqueness',
                Strict:true,
                Data:{
                    'VerifyUniqueness':params.Type,
                    'Target':params.Target
                }
            }).Result;
        }
        if(isUnique) {
            Core.ajaxRequest({
                URL:'SendVerificationCode',
                Strict:true,
                Data:{
                    'Type':params.Type,
                    'Target':params.Target
                }
            });
            Core.VerificationMode = true;
            Core.SetModal({
                title: 'Verification',
                closeText: 'Cancel',
                actionText: 'Confirm',
                reload: false
            })
            let data = {
                formID: 'mainModalForm',
                data: {
                    html: ['<p>We sent a code to "' + params.Target + '". Please enter it below to proceed</p>', 'html', '', false, []],
                    VerificationCode: ['', 'text', '', false, []],
                }
            };
            Core.BuildForm(data);
            Core.ShowModal(false);
        }else{
            toastr.error(params.Target+' is already associated with another account');
        }
    }
}
