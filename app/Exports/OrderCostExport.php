<?php

    namespace App\Exports;
    use Illuminate\Database\Eloquent\Collection;
    use Maatwebsite\Excel\Concerns\FromCollection;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

    class OrderCostExport implements FromCollection, ShouldAutoSize
    {
        private $data;

        /**
         * @return \Illuminate\Support\Collection
         */
        public function collection()
        {
            return new Collection($this->createData());
        }

        public function __construct($data)
        {
            $this->data = $data;
        }
        public function createData()
        {


            if (empty($this->data)) return false;
            $excelData   = [];
            $excelData[] = [
                'id',
                '仓库SKU',
                '产品标题',
                '采购负责人',
                '采购数量',
                '采购价',
                '平均单价',
                '总成本',
                '发货日期',
                '采购日期',

            ];
            foreach ($this->data as $key => $val) {
                $excelData[] = [
                    $val['id'],
                    $val['opPlatformSalesSkuQuantity'],
                    $val['productTitle'],
                    $val['buyerName'],
                    $val['quantity'],
                    $val['purchaseCost'],
                    $val['avgUnitPrice'],
                    $val['totalCost'],
                    $val['soShipTime'],
                    $val['pay_time'],
                ];
            }
            return $excelData;

        }
    }
