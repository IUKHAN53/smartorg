<div>
    @if($jsonData)
        <div class="my-orgchart-wrapper max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-screen">
            <div
                class="my-orgchart-topbar flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3  pb-3 space-y-3 sm:space-y-0">
                <div
                    class="my-orgchart-controls flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 w-full sm:w-auto">
                    <div class="relative w-full sm:w-auto" x-data="{ open: false }">
                        <div
                            class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2 w-full sm:w-auto">
                            <x-button onclick="setChartLayout('horizontal')" class="w-full sm:w-auto" color="secondary">
                                Horizontal
                            </x-button>
                            <x-button onclick="setChartLayout('compact')" class="w-full sm:w-auto" color="secondary">
                                Compact
                            </x-button>
                            <x-button
                                @click="open = ! open"
                                color="secondary"
                                class="w-full sm:w-auto"
                            >
                                Export
                            </x-button>
                        </div>
                        <ul
                            class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded shadow-lg z-20"
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            style="display: none;"
                        >
                            <li>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    onclick="myOrgChart.exportImg()"
                                >
                                    Export Current
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    onclick="myOrgChart.exportImg({full:true})"
                                >
                                    Export Full
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    onclick="myOrgChart.exportSvg()"
                                >
                                    Export SVG
                                </a>
                            </li>
                            <li>
                                <a
                                    href="#"
                                    class="block px-4 py-2 text-sm hover:bg-gray-100"
                                    onclick="myOrgChartDownloadPdf()"
                                >
                                    Export PDF
                                </a>
                            </li>
                        </ul>
                    </div>

                    <input
                        type="text"
                        class="my-orgchart-search-input form-input border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 w-full sm:w-64"
                        placeholder="Search by name..."
                        oninput="myOrgChartFilter(event)"
                    />

                    <x-button onclick="location.reload()" class="w-full sm:w-auto">
                        Refresh
                    </x-button>
                </div>
            </div>

            <div class="my-orgchart-container border border-gray-200 rounded-md p-4 bg-gray-50 w-full h-full"></div>

            <div
                id="myOrgChartNodeModal"
                class="my-orgchart-modal hidden fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 flex items-center justify-center z-50 px-4"
            >
                <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative">
                    <button
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
                        onclick="myOrgChartCloseModal()"
                    >
                        &times;
                    </button>
                    <h5 class="text-lg font-semibold mb-4" id="myOrgChartModalTitle">Node Details</h5>
                    <div id="myOrgChartModalBody" class="space-y-2"></div>
                    <div class="mt-4 flex justify-end">
                        <button
                            class="px-3 py-1 bg-gray-100 rounded text-gray-700 hover:bg-gray-200"
                            onclick="myOrgChartCloseModal()"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-yellow-100 text-yellow-700 border border-yellow-300 p-4 rounded">
            Please import JSON data first.
        </div>
    @endif
</div>

@push('styles')

@endpush

@push('scripts')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>
    <script src="https://unpkg.com/html2canvas@1.1.4/dist/html2canvas.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>

    <script>
        const myOrgChartData = @json($jsonData);
        let myOrgChart;

        let currentLayout = 'compact';

        function myOrgChartFilter(e) {
            const v = e.target.value.toLowerCase();
            myOrgChart.clearHighlighting();
            let d = myOrgChart.data();
            d.forEach(n => {
                n._expanded = false;
                n._highlighted = false;
            });
            if (v) {
                d.forEach(n => {
                    if (n.name && n.name.toLowerCase().includes(v)) {
                        n._highlighted = true;
                        n._expanded = true;
                    }
                });
            }
            myOrgChart.data(d).render().fit();
        }

        function myOrgChartOpenModal(id, name, position, image) {
            document.getElementById('myOrgChartModalTitle').innerText = name;
            document.getElementById('myOrgChartModalBody').innerHTML = `
              <div class="flex items-start space-x-4">
                <img
                  src="${image}"
                  alt="Profile"
                  class="rounded-full w-20 h-20 object-cover"
                />
                <div class="text-gray-700">
                  <p><strong>ID:</strong> ${id}</p>
                  <p><strong>Position:</strong> ${position}</p>
                </div>
              </div>
            `;
            document.getElementById('myOrgChartNodeModal').classList.remove('hidden');
        }

        function myOrgChartCloseModal() {
            document.getElementById('myOrgChartNodeModal').classList.add('hidden');
        }

        function myOrgChartDownloadPdf() {
            myOrgChart.exportImg({
                save: false,
                full: true,
                onLoad: (b64) => {
                    var p = new jspdf.jsPDF();
                    var i = new Image();
                    i.src = b64;
                    i.onload = function () {
                        p.addImage(i, 'JPEG', 5, 5, 595 / 3, ((i.height / i.width) * 595) / 3);
                        p.save('OrgChart.pdf');
                    };
                }
            });
        }

        function setChartLayout(layout) {
            if (layout === 'horizontal') {
                myOrgChart
                    .compact(false)
                    .render()
                    .fit();
                currentLayout = 'horizontal';
            } else if (layout === 'compact') {
                myOrgChart
                    .compact(true)
                    .render()
                    .fit();
                currentLayout = 'compact';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            myOrgChart = new d3.OrgChart()
                .container('.my-orgchart-container')
                .data(myOrgChartData)
                .nodeWidth(x => 220)
                .nodeHeight(x => 110)
                .childrenMargin(x => 50)
                .compactMarginBetween(x => 35)
                .compactMarginPair(x => 30)
                .neighbourMargin((a, b) => 20)
                .nodeUpdate(function () {
                    d3.select(this).select('.node-rect').attr('stroke', 'none');
                })
                .nodeContent(d => {
                    const highlightBorder = d.data._highlighted || d.data._upToTheRootHighlighted
                        ? '5px solid #70db70'
                        : '1px solid #E4E2E9';

                    const nodeWidth = Math.min(d.width, window.innerWidth * 0.8);
                    const nodeHeight = Math.min(d.height, 200);

                    return `
                      <div
                        style="
                          cursor:pointer;
                          width:${nodeWidth}px;
                          height:${nodeHeight}px;
                          position:relative;
                          background-color:#ffffff;
                          border-radius:10px;
                          border:${highlightBorder};
                        "
                        onclick="myOrgChartOpenModal('${d.data.id}','${d.data.name}','${d.data.title}','${d.data.image}')"
                      >
                        <div style="position:absolute;top:5px;right:8px;font-size:12px;color:#333;">#${d.data.id}</div>
                        <img
                          src="${d.data.image}"
                          style="
                            border-radius:50%;
                            width:40px;
                            height:40px;
                            object-fit:cover;
                            margin:10px;
                          "
                        />
                        <div style="margin-left:60px;margin-top:-40px;padding:10px;">
                          <div style="font-size:15px;color:#08011E;">${d.data.name}</div>
                          <div style="font-size:12px;color:#716E7B;">${d.data.title}</div>
                        </div>
                      </div>
                    `;
                })
                .compact(true)
                .render()
                .fit();

            window.addEventListener('resize', function () {
                myOrgChart.render().fit();
            });
        });
    </script>
@endpush
