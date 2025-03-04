<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\ProductionMaterial;
use App\Models\Purchase;
use App\Models\Sale;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    use ApiResponseHelpers;

    public function index(Request $request) {

        //Production defect rates. This should have total damaged productions against completed production for each month in the year
        //Profits made on each product after production
        //Pie chart of the months total caompleted production against damaged production
        //Total wasted materials per month
        //Recent Customers of products

        $date = date("Y-m-d");

        if($request->get('date')) $date = $request->get('date');

        $previous_month = date("Y-m-d", strtotime("-1 months",strtotime($date)));
        $recent_productions = $this->recentProductions(5);
        $completed_product_productions = $this->completedProductsProductions(5,$date);
        // return $completed_product_productions;

        $channel_rates = $this->saleChannelRatePlotValues($date);

        $selected_month_product_sales = $this->selectedMonthlyProductSales($date);
        // return $selected_month_product_sales;
        $previous_month_product_sales = $this->selectedMonthlyProductSales($previous_month);
        $sales_diff = $this->percentageDifference($selected_month_product_sales, $previous_month_product_sales);

        $selected_month_productions = $this->selectedMonthlyProductions($date);
        $previous_month_productions = $this->selectedMonthlyProductions($previous_month);

        $selected_month_purchases = $this->selectedMonthlyPurchases($date);
        // return $selected_month_purchases;
        $previous_month_purchases = $this->selectedMonthlyPurchases($previous_month);
        $purchases_cost_diff = $this->percentageDifference($selected_month_purchases, $previous_month_purchases);

        $selected_month_productions_cost = $this->getMonthProductionCost($selected_month_productions);
        $previous_month_productions_cost = $this->getMonthProductionCost($previous_month_productions);
        $production_cost_diff = $this->percentageDifference($selected_month_productions_cost, $previous_month_productions_cost);

        //Get month selected products sold
        $sold_products_selected_month = $this->monthlyProductSold($date);

        //Get month selected most materials used
        $getMostMaterialsUsed = $this->getMonthlyMostUsedMaterials($date);
        // return $sold_products_selected_month;

        return $this->respondWithSuccess([
            'recent_productions' => $recent_productions,
            'completed_product_productions' => $completed_product_productions,
            'top_moving_products_selected_month' => $sold_products_selected_month,
            'most_materials_used_selected_month' => $getMostMaterialsUsed,
            'card_analytics' => [
                [
                    'title' =>'Product Sales',
                    'value'=> $selected_month_product_sales,
                    'diff'=> $sales_diff
                ],
                [
                    'title' =>'Productions',
                    'value'=> $selected_month_productions_cost,
                    'diff'=> $production_cost_diff
                ],
                [
                    'title' =>'Purchases',
                    'value'=> $selected_month_purchases,
                    'diff'=> $purchases_cost_diff
                ]
                ],
                'channel_rate'=>$channel_rates
        ]);
    }
    public function recentProductions ($count) {
        $recent_productions = Production::where('business_id',request()->user()->business_id)
        ->whereNotIn('status',config('options.production_ends'))
        ->orderByDesc('updated_at')
        ->take($count)
        ->get();
        return $recent_productions;
    }

    public function completedProductsProductions ($count,$date) {
        $completed_product_productions = Production::with(['product:id,name,stock_quantity,image'])->where('business_id',request()->user()->business_id)
        ->where('status','completed')
        ->whereNotNull('product_id')
        ->whereMonth('completed_at',date('m',strtotime($date)))
        ->whereYear('completed_at',date('Y',strtotime($date)))
        ->orderByDesc('updated_at')
        ->take($count)
        ->get();
        return $completed_product_productions;
    }

    public function percentageDifference($current_value, $previous_value) {
        $value_diff = 0;
        if($previous_value == 0 && $current_value == 0) {
            $value_diff = 0;
        }
        elseif($previous_value == 0) {
            $value_diff = 100;
        }
        else {
            $value_diff = ($current_value - $previous_value) / $previous_value * 100;
        }

        return round($value_diff);
    }

    public function getMonthProductionCost($month_productions) {
        $selected_month_productions_cost = 0;

        foreach($month_productions as $month_production) {
            $selected_month_productions_cost += $month_production->labour_cost * $month_production->quantity;
            foreach($month_production->materials as $material) {
                $selected_month_productions_cost += $material->cost * $month_production->quantity;
            }
        }

        return $selected_month_productions_cost;
    }

    public function monthlyProductSold($date) {
        $monthly_product_sold = Sale::join('products','products.id','=','sales.product_id')
        ->selectRaw('products.name, sum(sales.quantity) as quantity, sum(sales.total_amount_paid) as amount_paid')
        ->where('sales.business_id',request()->user()->business_id)
        ->where('sales.order_status','completed')
        ->whereMonth('sales.sale_date_time',date('m',strtotime($date)))
        ->whereYear('sales.sale_date_time',date('Y',strtotime($date)))
        ->orderBy('quantity','desc')
        ->groupBy('products.name')
        ->get();
        return $monthly_product_sold;
    }

    public function selectedMonthlyProductSales($date) {
        $selected_monthly_product_sales = Sale::where('business_id',request()->user()->business_id)
        ->where('order_status','completed')
        ->whereMonth('sale_date_time',date('m',strtotime($date)))
        ->whereYear('sale_date_time',date('Y',strtotime($date)))
        ->sum('total_amount_paid');
        return $selected_monthly_product_sales;
    }

    public function selectedMonthlyProductions($date) {
        $selected_monthly_productions = Production::with(['materials'])->where('business_id',request()->user()->business_id)
        ->where('status','completed')
        ->whereMonth('completed_at',date('m',strtotime($date)))
        ->whereYear('completed_at',date('Y',strtotime($date)))
        ->get();
        return $selected_monthly_productions;
    }

    public function selectedMonthlyPurchases($date) {
        $selected_monthly_purchases = Purchase::where('business_id',request()->user()->business_id)
        ->where('status','Supplied')
        ->whereMonth('purchase_date',date('m',strtotime($date)))
        ->whereYear('purchase_date',date('Y',strtotime($date)))
        ->select(DB::raw('SUM(amount_paid) as total_paid'),DB::raw('SUM(tax) as total_tax'),DB::raw('SUM(shipping) as total_shipping'))
        ->first();
        $selected_monthly_purchases = $selected_monthly_purchases->total_paid + $selected_monthly_purchases->total_tax + $selected_monthly_purchases->total_shipping;
        return $selected_monthly_purchases;
    }

    public function getSalesChannelRates($date) {
        $channel_rates = Sale::selectRaw('sales_channel, sum(quantity) as quantity, sum(total_amount_paid) as amount_paid')
        ->where('business_id',request()->user()->business_id)
        ->whereIn('order_status',['completed','delivered'])
        ->whereMonth('sale_date_time',date('m',strtotime($date)))
        ->whereYear('sale_date_time',date('Y',strtotime($date)))
        ->groupBy('sales_channel')
        ->orderBy('sales_channel')
        ->get();

        return $channel_rates;
    }

    public function saleChannelRatePlotValues($date) {
        $data = $this->getSalesChannelRates($date);
        $plot_values = [];
        // { name: 'USA', value: 400, color: 'gauge-primary.1' },
        foreach(config('options.sales_channels') as $key=>$channel) {

            foreach($data as $key1=>$rate) {

                if($rate->sales_channel != $channel) {
                    continue;
                }
                $plot_values[] = [
                    'name' => Str::headline($rate->sales_channel).' ('.money($rate->amount_paid).')',
                    'value' => $rate->quantity,
                    'color' => config('options.sales_channels_colors')[$key1]
                ];

            }
        }

        return $plot_values;
    }

    public function getMonthlyMostUsedMaterials($date, $take=5) {

        $materials = ProductionMaterial::join('productions', 'productions.id','=', 'production_materials.production_id')
            ->join('materials', 'materials.id','=', 'production_materials.material_id')
            ->selectRaw('materials.id as id,
            materials.name as material_name,
            materials.image as image,
            materials.current_stock_level as current_stock_level,
            materials.minimum_stock_level as minimum_stock_level,
            sum(production_materials.quantity) as quantity_used,
            materials.unit_of_measurement as unit')
            ->whereMonth('productions.completed_at',date('m',strtotime($date)))
            ->whereYear('productions.completed_at',date('Y',strtotime($date)))
            ->where('productions.status','completed')
            ->where('productions.business_id',request()->user()->business_id)
            ->where('materials.is_component',false)
            ->groupBy(['production_materials.material_id','materials.id',
            'materials.name','materials.image','materials.current_stock_level',
            'materials.minimum_stock_level','materials.unit_of_measurement'])
            ->orderBy('quantity_used','desc')
            ->take($take)
            ->get();

            return $materials;
    }
}
