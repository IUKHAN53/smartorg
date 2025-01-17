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
        // Group items by their parentId
        $items = [];
        foreach ($data as $item) {
            $items[$item['parentId']][] = [
                'id' => (string)$item['id'],
                'name' => $item['fullname'],
                'title' => $item['position_name'],
                'image' => 'data:image/jpeg;base64,' . $item['imagepath'], // Convert imagepath to Base64
            ];
        }

        // Recursively build the hierarchy
        $buildTree = function ($parentId = null) use (&$buildTree, $items) {
            $tree = [];
            if (isset($items[$parentId])) {
                foreach ($items[$parentId] as $item) {
                    $item['children'] = $buildTree($item['id']);
                    $tree[] = $item;
                }
            }
            return $tree;
        };

        // OrgChart expects a single root node; adjust as needed
        $rootItems = $buildTree(null);
        return count($rootItems) === 1 ? $rootItems[0] : $rootItems;
    }


    public function render()
    {
        return view('livewire.chart-component', [
            'chartData' => $this->jsonData,
        ]);
    }
}
