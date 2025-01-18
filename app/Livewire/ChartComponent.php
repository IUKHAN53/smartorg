<?php

namespace App\Livewire;

use Livewire\Component;

class ChartComponent extends Component
{
    public $jsonData;

    public function mount($jsonData)
    {
        if(empty($jsonData)) {
            $this->jsonData = null;
        }else{
            $this->jsonData = $this->transformData(json_decode($jsonData, true));
        }
    }

    private function transformData($data)
{
    $transformed = [];

    foreach ($data as $item) {
        $transformed[] = [
            'id'            => (string)$item['id'],
            'parentId'      => $item['parentId'] ? (string)$item['parentId'] : null,
            'name'          => $item['fullname'],
            'title'  => $item['position_name'],
            'image'      => 'data:image/jpeg;base64,' . $item['imagepath'],
        ];
    }

    return $transformed;
}



    public function render()
    {
        return view('livewire.chart-component', [
            'chartData' => $this->jsonData,
        ]);
    }
}
