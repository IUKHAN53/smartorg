<div>
    @if($jsonData)
    <div class="my-orgchart-wrapper">
        <div class="my-orgchart-topbar d-flex align-items-center justify-content-between mb-3">
            <h2 class="my-orgchart-title">Organization Chart</h2>
            <div class="my-orgchart-search d-flex align-items-center">
                <div class="dropdown ms-2 mr-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="myOrgChart.exportImg()">Export Current</a></li>
                        <li><a class="dropdown-item" href="#" onclick="myOrgChart.exportImg({full:true})">Export Full</a></li>
                        <li><a class="dropdown-item" href="#" onclick="myOrgChart.exportSvg()">Export SVG</a></li>
                        <li><a class="dropdown-item" href="#" onclick="myOrgChartDownloadPdf()">Export PDF</a></li>
                    </ul>
                </div>
                <input
                    type="text"
                    class="my-orgchart-search-input form-control me-2"
                    placeholder="Search by name..."
                    oninput="myOrgChartFilter(event)"
                />
                <x-button onclick="location.reload()">Refresh</x-button>
            </div>
        </div>
        <div class="my-orgchart-container"></div>
        <div class="my-orgchart-modal modal fade" id="myOrgChartNodeModal" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="myOrgChartModalTitle">Node Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" id="myOrgChartModalBody"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
    </div>

    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
      .my-orgchart-wrapper {
        margin: 0 auto;
        max-width: 1200px;
      }
      .my-orgchart-container {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 10px;
        background-color: #f8fafc;
      }
      .my-orgchart-topbar {
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
      }
      .my-orgchart-title {
        font-size: 1.5rem;
        margin: 0;
        padding: 0;
      }
      .my-orgchart-search-input {
        width: 200px;
      }
      .my-orgchart-modal .modal-content {
        border-radius: 8px;
      }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>
    <script src="https://unpkg.com/html2canvas@1.1.4/dist/html2canvas.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script>
      const myOrgChartData = @json($jsonData);
      let myOrgChart;
      function myOrgChartFilter(e) {
        const v = e.target.value.toLowerCase();
        myOrgChart.clearHighlighting();
        let d = myOrgChart.data();
        d.forEach(n => {n._expanded=false;n._highlighted=false;});
        if(v) d.forEach(n=>{if(n.name&&n.name.toLowerCase().includes(v)){n._highlighted=true;n._expanded=true;}});
        myOrgChart.data(d).render().fit();
      }
      function myOrgChartOpenModal(id,name,position,image) {
        document.getElementById('myOrgChartModalTitle').innerText=name;
        document.getElementById('myOrgChartModalBody').innerHTML=`
          <div class="d-flex">
            <img src="${image}" alt="Profile" class="rounded-circle me-3" style="width:80px;height:80px;object-fit:cover;" />
            <div>
              <p><strong>ID:</strong> ${id}</p>
              <p><strong>Position:</strong> ${position}</p>
            </div>
          </div>
        `;
        new bootstrap.Modal(document.getElementById('myOrgChartNodeModal'),{}).show();
      }
      function myOrgChartDownloadPdf() {
        myOrgChart.exportImg({
          save:false,
          full:true,
          onLoad:(b64)=>{
            var p=new jspdf.jsPDF();var i=new Image();i.src=b64;i.onload=function(){
              p.addImage(i,'JPEG',5,5,595/3,((i.height/i.width)*595)/3);
              p.save('OrgChart.pdf');
            };
          }
        });
      }
      document.addEventListener('DOMContentLoaded',function(){
        myOrgChart=new d3.OrgChart()
        .container('.my-orgchart-container')
        .data(myOrgChartData)
        .nodeWidth(x=>220)
        .nodeHeight(x=>110)
        .childrenMargin(x=>50)
        .compactMarginBetween(x=>35)
        .compactMarginPair(x=>30)
        .neighbourMargin((a,b)=>20)
        .nodeUpdate(function(){d3.select(this).select('.node-rect').attr('stroke','none');})
        .nodeContent(d=>{
          const c='#FFFFFF';
          const bs=d.data._highlighted||d.data._upToTheRootHighlighted?'5px solid #70db70':'1px solid #E4E2E9';
          return `
            <div style="cursor:pointer;width:${d.width}px;height:${d.height}px;position:relative;background-color:${c};border-radius:10px;border:${bs};" onclick="myOrgChartOpenModal('${d.data.id}','${d.data.name}','${d.data.title}','${d.data.image}')">
              <div style="position:absolute;top:5px;right:8px;font-size:12px;color:#333;">#${d.data.id}</div>
              <img src="${d.data.image}" style="border-radius:50%;width:40px;height:40px;object-fit:cover;margin:10px;" />
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
      });
    </script>
    @endpush
    @else
    <div class="alert alert-warning">
        Please import JSON data first.
    </div>
    @endif
</div>
