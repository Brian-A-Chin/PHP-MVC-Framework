
let DateColumns = [];

$(window).on("load", function () {

    if (!$('.pagination-table').length){return false}
    let Configuration = {
        searchQueryParam: 'Search',
        sortParam: 'Sort',
        sortDelimiter: ':',
        categoryParam: 'Category',
        pagingParam: 'CurrentPage',
        queryLimitParam: 'PageSize',
        queryLimitVal: '10',
        startDateParam: 'StartDate',
        endDateParam: 'EndDate',
        mainSearchId: 'main-search',
        DateColumn: 'DateColumn',
    }
    var DependentOutput = '';
    var CurrentSort = {};
    var CurrentPageNumber = Core.URLObject.get(Configuration.pagingParam) !== null ? Core.NumberFormat(Core.URLObject.get(Configuration.pagingParam)) : -1;



    var RangeStart = Core.URLObject.get(Configuration.startDateParam) === null ? moment().subtract(29, 'days') : moment(Core.URLObject.get(Configuration.startDateParam));
    var RangeEnd = Core.URLObject.get(Configuration.endDateParam) === null ? moment() : moment(Core.URLObject.get(Configuration.endDateParam));

    var defaultRangePeriod = $('.rangepicker').attr('data-default-range') != undefined ? $('.rangepicker').attr('data-default-range') : 'All Time';

    var pagingParameters = {};

    function setSelect(data) {
        if (data.selects.length > 0) {
            $.each(data.selects, function (key, value) {
                var result = Core.URLObject.get(value);
                if (result !== null && $('#' + value + ' option[value="' + result + '"]').length > 0) {
                    $('#' + value).val(result);
                }
            });
            $('.loader').fadeOut();
        }
    }



    function GetSortClass(sort) {
        return sort === 'DESC' ? '<i class="fas fa-angle-down sort-i" aria-hidden="true"></i>' : '<i class="fas fa-angle-up sort-i" aria-hidden="true"></i>';
    }


    function InitSorting() {
        if (Core.URLObject.get(Configuration.sortParam) !== null) {
            var sortCloumns = Core.URLObject.get(Configuration.sortParam).split(Configuration.sortDelimiter);
            var i = 0;
            $.each(sortCloumns, function (k, v) {
                if (i % 2 === 0) {
                    var $this = $('#' + v);
                    var sort = sortCloumns[k + 1];
                    CurrentSort[v] = sort;
                    $this.attr('data-sort', sort);
                    $this.append(GetSortClass(sort));
                    $this.append('<i class="fas fa-times-circle delsort-i" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Omit ' + v + ' from sorting"></i>');
                    if (i === 0) {
                        $('.table-responsive').prepend('<small id="sortPriority">Sorting on: <span data-toggle="tooltip" data-placement="top" title="Click to omit. Drag to reorder"> ' + v + ' </span></small>');
                        $this.css({ 'background-color': '#d9534f', 'color': '#fff' });
                    }
                    else {
                        $('#sortPriority').append('<span data-toggle="tooltip" data-placement="top" title="Click to omit. Drag to reorder"> ' + v + ' </span>');
                    }

                }
                i++;
            });
        }
        $.each($('.setSort'), function () {
            if ($(this).attr('data-sort').length && !CurrentSort.hasOwnProperty(this.id)) {
                var sort = $(this).attr('data-sort').toUpperCase();
                CurrentSort[this.id] = sort;
                $(this).append(GetSortClass(sort));
                $(this).append('<i class="fas fa-times-circle delsort-i" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Omit from sorting"></i>');
            }
        });

    }

    function setSort(params) {
        var dependentDelimiter = Object.keys(CurrentSort).length > 0 && params.override ? Configuration.sortDelimiter : '';
        var sortParam = Configuration.sortParam;
        var target = params.target;
        var value = params.value;
        if (Object.keys(CurrentSort).length > 0) {
            if (params.action === 'set') {
                if (params.override) {
                    delete CurrentSort[target];
                    DependentOutput = target + Configuration.sortDelimiter + value;
                }
                else {
                    CurrentSort[target] = value;
                }

            }
            if (params.action === 'delete') {
                delete CurrentSort[target];
            }
            if (Object.keys(CurrentSort).length !== 0) {
                CurrentSort = jQuery.map(CurrentSort, function (t, v) {
                    return v + Configuration.sortDelimiter + t;
                });
                Core.URLObject.set(sortParam, DependentOutput + dependentDelimiter + CurrentSort.join(Configuration.sortDelimiter));
            }
            else {
                Core.URLObject.delete(sortParam);
            }
        }
        else {
            Core.URLObject.set(sortParam, target + Configuration.sortDelimiter + value);
        }
        window.location.href = decodeURIComponent(Core.URL)
    }
    function SetSearch() {
        var searchValue = Core.URLObject.get(Configuration.searchQueryParam);
        if (Core.URLObject.get(Configuration.searchQueryParam) || (Core.URLObject.get(Configuration.startDateParam) && Core.URLObject.get(Configuration.endDateParam))) {
            if (Core.URLObject.get(Configuration.searchQueryParam) && $('#' + Configuration.categoryParam+' option[value='+Core.URLObject.get(Configuration.categoryParam)+']').length)
                $('#' + Configuration.mainSearchId).val(searchValue);
            if (!$.trim($("table tbody").html())) {
                var response = searchValue !== null ? 'Your search - ' + searchValue.substr(0, 50) + ' - did not return any results.' : 'There are no records between the dates: ' + Core.URLObject.get(Configuration.startDateParam) + ' and ' + Core.URLObject.get(Configuration.endDateParam);
                $("table").after('<div class="alert alert-danger" role="alert">' + response + '</div >');
                $("table thead, .paging-dependent").hide();
            }
        }
    }

    function setDateColumns(){
        $(".pagination-table thead tr th").each(function () {
            var type = $(this).data('type');
            if (type!= undefined)
                type = type.trim();
            if ($.inArray(type, ['datetime', 'smalldatetime']) !== -1) {
                if (this.hasAttribute('data-alt-name')) {
                    DateColumns.push($(this).attr('data-alt-name'));
                } else {
                    DateColumns.push($(this).text().replace(/\s/g, ''));
                }
            }
        });
        if (DateColumns.length != 0) {
           if(Core.URLObject.get(Configuration.DateColumn) != null){
               Core.URLObject.set(Configuration.DateColumn, Core.URLObject.get(Configuration.DateColumn));
           }else{
               Core.URLObject.set(Configuration.DateColumn, DateColumns[0]);
           }
            window.history.pushState(null, null, Core.URL);
            var currentDateColumn = Core.URLObject.get(Configuration.DateColumn);

            $.each(DateColumns, function (key, value) {
                $('#DateColumn').append($('<option></option>').val(value).text(value));
            });

            if (currentDateColumn == null) {
                $('.dateRangeBtnContainer').hide();
            } else {
                setSelect({
                    selects: ['DateColumn']
                });
                $('#rangeFilterMsg').text('=')
            }

            $('.DateColumnSelector').show();
        } else {
            $('.dateRangeBtnContainer').hide();
        }
    }





    function Search() {
        var term = $('#' + Configuration.mainSearchId).val();
        var queryString = Configuration.searchQueryParam;
        var categoryParam = Configuration.categoryParam;
        if (term.length > 0) {
            Core.URLObject.set(Configuration.pagingParam, 1);
            Core.URLObject.set(categoryParam, $('#' + categoryParam).val());
            Core.URLObject.set(queryString, term);
        } else {
            Core.URLObject.delete(queryString);
            Core.URLObject.delete(categoryParam);
        }
        window.location.href = Core.URL;
    }


    function InitPaging(force) {
        var pagingParam = Configuration.pagingParam;
        if ((Core.URLObject.get(pagingParam) === null || Core.NumberFormat(Core.URLObject.get(pagingParam)).length === 0 || force) && !Core.ExceptionMode) {
            Core.URLObject.set(pagingParam, 1);
            Core.URLObject.set(Configuration.queryLimitParam, Configuration.queryLimitVal);
            window.history.pushState(null, null, Core.URL);
            CurrentPageNumber = Core.URLObject.get(Configuration.pagingParam) !== null ? Core.NumberFormat(Core.URLObject.get(Configuration.pagingParam)) : -1;

        }
        MarkPage();

        setDateColumns();

        setSelect({
            selects: ['PageSize', 'Category']
        });

    }

    function MarkPage() {

        if (CurrentPageNumber == undefined || CurrentPageNumber < 0) {
            InitPaging(true)
        } else {
            $('#page-' + CurrentPageNumber).addClass('page-active');

            if (pagingParameters.TotalPages == 1) {
                $('.pagnate,.page-jump').addClass('page-disabled');
            }
            if (pagingParameters.CurrentPage == 1) {
                $('[data-direction=pre]').addClass('page-disabled');
                $('.jump-to-start').addClass('page-disabled');
            } else if (pagingParameters.CurrentPage == pagingParameters.TotalPages) {
                $('[data-direction=fwd]').addClass('page-disabled');
                $('.jump-to-end').addClass('page-disabled');
            }
        }

    }

    function GoToPageNumber(pageNumer) {
        if (Core.NumberFormat(Core.URLObject.get(Configuration.pagingParam)).length !== 0) {
            Core.URLObject.set(Configuration.pagingParam, pageNumer);
            window.location.href = Core.URL;
        }
    }


    function RangeCB(RangeStart, RangeEnd) {
        if (Core.URLObject.get(Configuration.startDateParam) !== null && Core.URLObject.get(Configuration.endDateParam) !== null) {
            $('.rangepicker span').html(RangeStart.format('MMMM D, YYYY') + ' - ' + RangeEnd.format('MMMM D, YYYY'));
        }
        else {
            $('.rangepicker span').html(defaultRangePeriod);
        }
    }

    var RangeParams = {
        opens: 'auto',
        drops: 'up',
        startDate: RangeStart,
        endDate: RangeEnd,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],

        }
    };
    RangeParams.ranges[defaultRangePeriod] = [false, false];


    function ReorderSort(startPosition, endPosition) {
        if (startPosition !== endPosition) {
            var newSort = {};
            $.each($('#sortPriority span'), function () {
                var key = $(this).text().trim();
                newSort[key] = CurrentSort[key];
            });
            CurrentSort = newSort;
            setSort({
                action: 'reorder'
            });
        }
    }

    InitSorting();
    $("#sortPriority").sortable({
        placeholder: "sort-order-placeholder",
        start: function (event, ui) {
            ui.placeholder.width(ui.item.width());
            ui.item.toggleClass("sort-order-placeholder");
            startPosition = ui.item.index();
            ui.item.css({
                'color': 'red',
                'width': 'max-content'
            });
        },
        stop: function (event, ui) {
            ui.item.toggleClass("sort-order-placeholder");
            endPosition = ui.item.index();
            ui.item.removeAttr('style');
            ReorderSort(startPosition, endPosition);
        }
    });

    SetSearch();
    $('select option').each(function () {
        this.text = Core.ReplaceUnderscores(this.text)
    });

    $(document).on('keypress', function (e) {
        if (e.which == 13 && 1 == 4) {
            Search();
        }
    });



    $('.rangepicker').on('show.daterangepicker', function (ev, picker) {
        $('.overlay').fadeIn();
    });

    $('.rangepicker').on('hide.daterangepicker', function (ev, picker) {
        $('.overlay').fadeOut();
    });

    $('.rangepicker').on('apply.daterangepicker', function (ev, picker) {
        if (picker.startDate.isValid()) {
            Core.URLObject.set(Configuration.startDateParam, picker.startDate.format('YYYY-MM-DD'));
            Core.URLObject.set(Configuration.endDateParam, picker.endDate.add(1, 'days').format('YYYY-MM-DD'));
        }
        else {
            $('.rangepicker span').html('All Time');
            Core.URLObject.delete(Configuration.startDateParam);
            Core.URLObject.delete(Configuration.endDateParam);
        }
        Core.URLObject.set(Configuration.pagingParam, 1);
        window.location.href = Core.URL;
    });

    $(document).on("mouseover", ".init-paging", function (event) {
        $(this).removeClass("init-paging");
        $(this).attr("href", $(this).attr("href") + '&' + Configuration.pagingParam + '=1&' + Configuration.queryLimitParam + '=' + Configuration.queryLimitVal);
    });

    $(document).on("click", ".disabled, .page-disabled", function (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    });


    $(document).on("click", ".grouped-input-submit", function (event) {
        if ($(this).hasClass('stopPropagation')) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
        }
        $('.loader').fadeIn();
        var url = $(this).attr('data-url');
        var formData = {};
        $.each($('.' + $(this).attr('data-group')), function (key, value) {
            formData[value.getAttribute('name')] = value.value;
        });
        var reload = false;
        if ($(this)[0].hasAttribute("data-reload")) {
            reload = ($(this).attr("data-reload") === "true");
        }


        if ($(this)[0].hasAttribute("data-confirm")) {
            showDialog(false);
            Core.Purgatory.push({
                trigger: 'ajaxRequest',
                data: {
                    formData: formData,
                    reload: reload,
                    url: url
                }
            });
        } else {
            ajaxRequest({
                formData: formData,
                reload: reload,
                url: url
            })
        }
    });

    $(document).on("click", ".main-search-button", function () {
        Search();
    });

    $(document).on("click", ".action-trigger", function () {
        switch ($(this).attr('data-type')) {
            case 'form':
                $('#' + $(this).attr('data-trigger')).submit();
                break;
            case 'exportTable':
                exportTable();
                break;
            default:
                //Core.HideModal();
                break;
        }

    });


    $(document).on("change", ".urlTuner", function () {
        var param = this.id;
        Core.URLObject.set(param, this.value);
        if (param === Configuration.queryLimitParam)
            Core.URLObject.set(Configuration.pagingParam, 1);
        window.location.href = Core.URL;
    });


    $(document).on("click", ".page-item", function () {
        if (!$(this).hasClass('pagnate')) {
            var pageNumber = Core.NumberFormat(this.id);
            GoToPageNumber(pageNumber);
        }
    });


    $(document).on("click", ".page-jump", function () {
        var pageNumber = 1;
        if (!$(this).hasClass('jump-to-start')) {
            pageNumber = pagingParameters.TotalPages;
        }

        GoToPageNumber(pageNumber);
    });

    $(document).on("click", ".pagnate", function () {
        var pageNumber = $(this).attr('data-direction') === 'fwd' ? CurrentPageNumber += 1 : CurrentPageNumber -= 1;
        GoToPageNumber(pageNumber);
    });


    $(document).on("click", ".setSort", function (event) {
        if (!$(event.target).hasClass('delsort-i')) {
            var $this = event.target.nodeName === 'I' ? $(event.target).parent('th') : $(event.target);
            var target = $this.get(0).id;
            var value = $this.attr('data-sort') === 'DESC' || $this.attr('data-sort').length === 0 ? 'ASC' : 'DESC';
            var request = {
                action: 'set',
                override: false,
                target: target,
                value: value
            };
            Core.DelayClicks++;
            if (Core.DelayClicks === 1) {
                Core.DelayTimer = setTimeout(function () {
                    setSort(request);
                    Core.DelayClicks = 0;
                }, Core.DELAY);
            } else {
                clearTimeout(Core.DelayTimer);
                Core.DelayClicks = 0;
                request.override = true;
                request.value = $this.attr('data-sort');
                setSort(request);
            }
        } else {
            setSort({
                action: 'delete',
                target: $(event.target).parent('th').get(0).id
            });

        }
    }).on("dblclick", function (e) {
        e.preventDefault();
    });

    $(document).on("click", "#sortPriority span", function (event) {
        setSort({
            action: 'delete',
            target: $(this).text().trim()
        });
    });


    $(document).on("click", ".dateColumnPicker", function (event) {

        if (Core.URLObject.get(Configuration.DateColumn) == null) {

            var formData = {
                formID: 'mainModalForm',
                data: {
                    DateColumn: ['Please select 1 option below', 'radio', '', false, [
                        {
                            comedate: 'val1',
                            comedate2: 'val2',
                        }
                    ]],


                }
            };
            Core.SetModal({
                URL: '#',
                title: 'Please choose a column to filter by',
                closeText: 'Cancel',
                actionText: 'Select',
                override: 'true',
                reload: false,
            });
            Core.BuildForm(formData);
            Core.ShowModal('show');

        } else {
            $(this).addClass('rangepicker');
            $(this).removeClass('dateColumnPicker');
            $(this).trigger('click');
        }
    });
    $('.rangepicker').daterangepicker(RangeParams, RangeCB);
    RangeCB(RangeStart, RangeEnd);



    if ($(".pagination-table").length) {
        var dragCheck = false;
        $(".pagination-table").each(function (key, value) {
            var id = this.id;
            $(".pagination-table").dragtable({
                dragaccept: '.draggable-col',
                excludeFooter: true,
                persistState: function (table) {
                    if (!window.sessionStorage) return;
                    var ss = window.sessionStorage;
                    id = table.el.closest('table').attr('id');
                    table.el.find('th').each(function (i) {
                        if (this.id != '') { table.sortOrder[this.id] = i; }
                    });
                    ss.setItem(id, JSON.stringify(table.sortOrder));
                },
                restoreState: eval('(' + window.sessionStorage.getItem(id) + ')')
            });
        });

    }



    function exportTable() {
        var fileName = $('input[name="exportFileName"]').val();
        var sheet_names = [];
        $('table').each(function (key, value){
            if (this.hasAttribute('data-name')) {
                sheet_names.push($(this).attr('data-name'));
            }
        });



        var readyState = false
        var exludeColumns = "";

        if ($('select[name="exportExcludeColumns"]').val() == undefined) {
            readyState = true;
        } else {
            if ($('select[name="exportExcludeColumns"]').val().length !== parseInt($('select[name="exportExcludeColumns"]').attr('size'))) {
                readyState = true;
                exludeColumns = $('select[name="exportExcludeColumns"]').val();
            }
        }

        if (Core.FormIsFilled({
            target: 'mainModalForm',
            exclude: ['exportExcludeColumns'],
            highlight: true
        })) {
            if (readyState) {
                $('.shorten').each(function (key, value) {
                    var fullTxt = $(this).attr('data-full');
                    var excerpt = $(this).text();
                    $(this).text(fullTxt);
                    $(this).attr('data-full', excerpt)
                });
                $('.loader').fadeIn("slow", function () {
                    var type = $.inArray($('select[name="exportFileType"]').val(), Core.ExportTypes) !== -1 ? $('select[name="exportFileType"]').val() : 'xlsx';
                    $('.pagination-table').tableExport({
                        fileName: fileName,
                        type: type,
                        mso: {
                            fileFormat: type,
                            worksheetName: sheet_names
                        },
                        ignoreColumn: exludeColumns
                    });
                    $('.shorten').each(function (key, value) {
                        var fullTxt = $(this).text()
                        var excerpt = $(this).attr('data-full');
                        $(this).text(excerpt);
                        $(this).attr('data-full', fullTxt)
                    });
                    $('.loader').fadeOut();
                    toastr.success('Export successfull');
                });
            }
            Core.HideModal();
        }
    }

    $(document).on("click", "#export", function () {
        var modalTitle = 'Export';
        var ExportName = '';
        if (this.hasAttribute('data-title')) {
            ExportName = $(this).attr('data-title');
        }
        if (this.hasAttribute('data-modal-title')) {
            modalTitle = $(this).attr('data-modal-title');
        }
        Core.SetModal({
            URL: '#',
            title: modalTitle,
            type: 'exportTable',
            actionText: 'Export',
            reload: false,
            override: 'true',
        })

        var columns = [];
        var columnIndexes = [];
        $('.pagination-table th').each(function () {
            if (!$(this).hasClass('hidden')) {
                if ($(this).text().trim().length > 0) {
                    columns.push($(this).text());
                    columnIndexes.push($(this).index());
                }
            }
        });

        var params = {
            formID: 'mainModalForm',
            data: {
                exportFileName: ['File name', 'text', ExportName, false, []],
                exportFileType: ['File type', 'select', '', false, Core.ExportTypes, Core.ExportNiceNames],
                exportExcludeColumns : ['', 'hidden', '', false, columnIndexes, columns]
            }
        };

        //exclusion ability
        if (!this.hasAttribute('data-omit-exclusions')) {
            console.log(this.hasAttribute('data-omit-exclusions'))
            params.data.exportExcludeColumns = ['Exclude column(s)', 'multiSelect', '', false, columnIndexes, columns];
            params.data.html1 = ['<small class="text-muted">Hold down the Ctrl (windows) or Command (Mac) button to select multiple options.</small>', 'html'];
        }

        Core.BuildForm(params);
        Core.ShowModal('show');

    });


    InitPaging(false);



});





