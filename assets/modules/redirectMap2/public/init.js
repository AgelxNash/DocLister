(function ($) {
    $(function () {
        $.AJAXTable = {
            clickAction: function (el) {
                jQuery.ajax({
                    type: 'get',
                    url: $(el).attr('href'),
                    success: function (string) {
                        $("#logBlock").html(string);
                        $.AJAXTable.reloadGrid();
                    }
                });
            },
            reloadGrid: function () {
                jQuery.ajax({
                    type: 'get',
                    url: window.location.href,
                    data: {action: 'lists'},
                    success: function (string) {
                        $("#ajaxTable").html(string);
                        $(".click").editable($.URLAction.saveValue, {
                            id: 'data',
                            data: 'set',
                            type: 'text',
                            onblur: 'submit',
                            cssclass: 'editclass',
                            onsubmit: function (settings, selfObj) {
                                $("#logBlock").html('');
                                var out = false;
                                var match = $(selfObj).attr('id').match(/^(.*)_(\d+)$/);
                                if (match !== null) {
                                    switch (match[1]) {
                                        case 'page':
                                        {
                                            out = /^\d+$/.test($(selfObj).find('input').val());
                                            if (!out) {
                                                alert('Необходимо ввести число');
                                            }
                                            break;
                                        }
                                        case 'uri':
                                        {
                                            jQuery.ajax({
                                                type: "post",
                                                url: $.URLAction.checkUniq,
                                                async: false,
                                                data: {
                                                    data: $(selfObj).attr('id'),
                                                    value: $(selfObj).find('input').val()
                                                },
                                                success: function (string) {
                                                    if (string != 'true') {
                                                        $(selfObj).find('input').addClass('errorInput');
                                                        alert(string);
                                                    } else {
                                                        out = true;
                                                    }
                                                }
                                            });
                                            break;
                                        }
                                        default:
                                        {
                                            out = true;
                                        }
                                    }
                                }
                                return out;
                            },
                            loadtype: 'POST',
                            loadurl: $.URLAction.getValue,
                            indicator: "<img src='/assets/js/jeditable/img/indicator.gif'>",
                            placeholder: "Для редактирования нужно кликнуть...",
                            loadtext: "Загрузка...",
                            tooltip: "Для редактирования нужно кликнуть...",
                            style: "inherit",
                            callback: function (value, settings) {
                                $.AJAXTable.reloadGrid();
                            }
                        });
                    }
                });
            }
        };

        $.AJAXTable.reloadGrid();
        $('#ajaxTable').on('click', '.fulldel_action', function (e) {
            $.AJAXTable.clickAction($(this));
            e.preventDefault();
        });
        $('#ajaxTable').on('click', '.is_active', function (e) {
            $.AJAXTable.clickAction($(this));
            e.preventDefault();
        });

        if ($('#csvUpload').length) {
            $('#csvUpload').hide();
            if ($("#showCsvUpload").length) {
                $("#showCsvUpload").click(function (e) {
                    e.preventDefault();
                    $(this).hide();
                    $('#csvUpload').show();
                });
            }
        }
        ;

        if (typeof FileAPI != 'undefined') {
            $('#csvUpload').fileapi({
                url: $.URLAction.csvUpload,
                multiple: true,
                maxSize: 5 * FileAPI.MB,
                autoUpload: true,
                accept: '.txt,.csv',
                elements: {
                    size: '.js-size',
                    active: { show: '.js-upload', hide: '.js-browse' },
                    progress: '.js-progress'
                },
                onSelect: function (evt, ui) {
                    var file = ui.files[0];
                    if (!file) {
                        alert('Выбран не корректный файл или его размер превышает допустимые пределы (5 Мбайт)');
                    }
                },
                onFileComplete: function (evt, ui) {
                    if (!ui.error) {
                        if (ui.result.message) {
                            $("#logBlock").html(ui.result.message);
                            $.AJAXTable.reloadGrid();
                        } else {
                            alert('Не удалось получить ответ от сервера. Попробуйте повторить ошибку позже');
                        }
                    } else {
                        alert('Во время загрузки файла произошшла ошибка. Проверьте, соблюдены ли все стандарты для загружаемых файлов');
                    }
                }
            });
        }
        ;
    });
})(jQuery);