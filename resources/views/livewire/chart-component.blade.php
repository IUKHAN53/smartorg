<div>
    @if($jsonData)
        <div id="edit-panel" class="view-state">
            <input type="text" id="key-word">
            <button type="button" id="btn-filter-node">Filter</button>
            <button type="button" id="btn-cancel">Cancel</button>
        </div>
        <div id="orgchart-container" style="height: 100%;"></div>
        @push('styles')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/css/jquery.orgchart.min.css">
            <style>
                .orgchart .node.matched {
                    background-color: rgba(238, 217, 54, 0.5);
                }

                .orgchart .hierarchy.first-shown::before {
                    left: calc(50% - 1px);
                    width: calc(50% + 1px);
                }

                .orgchart .hierarchy.last-shown::before {
                    width: calc(50% + 1px);
                }

                .orgchart .hierarchy.first-shown.last-shown::before {
                    width: 2px;
                }

                #edit-panel {
                    text-align: center;
                    margin: 0.5rem;
                    padding: 0.5rem;
                    border: 1px solid #aaa;
                }

                #edit-panel * {
                    font-size: 1rem;
                }

                button, input {
                    padding: 0.5rem 1rem;
                }

                #orgchart-container {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100%;
                    overflow: auto;
                    border: 1px solid #ccc;
                    border-radius: 8px;
                    padding: 10px;
                }

                #toolbar button, #toolbar input {
                    margin: 5px;
                    padding: 5px 10px;
                    font-size: 14px;
                }

                .orgchart {
                    background: #f9fafb;
                }

                .avatar {
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    margin-bottom: 5px;
                }

                .custom-node {
                    text-align: center;
                    padding: 10px;
                    border: 1px solid #ccc;
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                }

                .custom-node img {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    margin-bottom: 10px;
                }

                .custom-node .name {
                    font-weight: bold;
                    font-size: 16px;
                    margin-bottom: 5px;
                }

                .custom-node .title {
                    font-size: 14px;
                    color: #555;
                }

                .custom-node .position-nr {
                    font-size: 12px;
                    color: #888;
                    margin-top: 5px;
                }
            </style>
        @endpush

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/orgchart@2.1.9/dist/js/jquery.orgchart.min.js"></script>
            <script type="text/javascript"
                    src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script>
                const chartData = @json($chartData);

                // Initialize OrgChart
                const $chartContainer = $('#orgchart-container').orgchart({
                    exportButton: true,
                    exportFilename: 'MyOrgChart',
                    pan: true,
                    zoom: true,
                    data: chartData,
                    nodeContent: 'title',
                    direction: 't2b',
                    'toggleSiblingsResp': true,
                    createNode: function ($node, data) {
                        const customNode = `
                        <div class="custom-node">
                            <img src="${data.image}" alt="${data.name}" />
                            <div class="name">${data.name}</div>
                            <div class="title">${data.title}</div>
                            <div class="position-nr">#${data.id}</div>
                        </div>
                    `;
                        $node.html(customNode);
                    }
                });

                function loopChart($hierarchy) {
                    var $siblings = $hierarchy.children('.nodes').children('.hierarchy');
                    if ($siblings.length) {
                        $siblings.filter(':not(.hidden)').first().addClass('first-shown')
                            .end().last().addClass('last-shown');
                    }
                    $siblings.each(function (index, sibling) {
                        loopChart($(sibling));
                    });
                }

                function filterNodes(keyWord) {
                    if (!keyWord.length) {
                        window.alert('Please type key word firstly.');
                        return;
                    } else {
                        var $chart = $('.orgchart');
                        // disalbe the expand/collapse feture
                        $chart.addClass('noncollapsable');
                        // distinguish the matched nodes and the unmatched nodes according to the given key word
                        $chart.find('.node').filter(function (index, node) {
                            return $(node).text().toLowerCase().indexOf(keyWord) > -1;
                        }).addClass('matched')
                            .closest('.hierarchy').parents('.hierarchy').children('.node').addClass('retained');
                        // hide the unmatched nodes
                        $chart.find('.matched,.retained').each(function (index, node) {
                            $(node).removeClass('slide-up')
                                .closest('.nodes').removeClass('hidden')
                                .siblings('.hierarchy').removeClass('isChildrenCollapsed');
                            var $unmatched = $(node).closest('.hierarchy').siblings().find('.node:first:not(.matched,.retained)')
                                .closest('.hierarchy').addClass('hidden');
                        });
                        // hide the redundant descendant nodes of the matched nodes
                        $chart.find('.matched').each(function (index, node) {
                            if (!$(node).siblings('.nodes').find('.matched').length) {
                                $(node).siblings('.nodes').addClass('hidden')
                                    .parent().addClass('isChildrenCollapsed');
                            }
                        });
                        // loop chart and adjust lines
                        loopChart($chart.find('.hierarchy:first'));
                    }
                }

                function clearFilterResult() {
                    $('.orgchart').removeClass('noncollapsable')
                        .find('.node').removeClass('matched retained')
                        .end().find('.hidden, .isChildrenCollapsed, .first-shown, .last-shown').removeClass('hidden isChildrenCollapsed first-shown last-shown')
                        .end().find('.slide-up, .slide-left, .slide-right').removeClass('slide-up slide-right slide-left');
                }

                $('#btn-filter-node').on('click', function () {
                    filterNodes($('#key-word').val());
                });

                $('#btn-cancel').on('click', function () {
                    clearFilterResult();
                });

                $('#key-word').on('keyup', function (event) {
                    if (event.which === 13) {
                        filterNodes(this.value);
                    } else if (event.which === 8 && this.value.length === 0) {
                        clearFilterResult();
                    }
                });

            </script>
        @endpush
    @else
        <div class="alert alert-warning">Import Json Data first</div>
    @endif
</div>
