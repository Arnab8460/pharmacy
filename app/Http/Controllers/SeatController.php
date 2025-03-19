<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    protected $auth;
    public $back_url = null;

    public function seat_report(Request $request)
    {
        $delete_temp_table    =    "DROP TABLE IF EXISTS admitted_counts;";
        DB::select($delete_temp_table);
        $temp_table    =    " CREATE TEMPORARY TABLE admitted_counts AS
    SELECT
    s_inst_code AS admitted_college,
    s_trade_code as admitted_trade,
    -- Known categories, adjust as necessary
	SUM(CASE WHEN s_alloted_category = 'tfw' THEN total_alloted ELSE 0 END) AS tfw_admitted,
	SUM(CASE WHEN s_alloted_category = 'ews' THEN total_alloted ELSE 0 END) AS ews_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfllq' THEN total_alloted ELSE 0 END) AS sqfllq_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqollq' THEN total_alloted ELSE 0 END) AS sqollq_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqollq' THEN total_alloted ELSE 0 END) AS dqollq_admitted,
	SUM(CASE WHEN s_alloted_category = 'exs' THEN total_alloted ELSE 0 END) AS exs_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfpc' THEN total_alloted ELSE 0 END) AS sqfpc_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfgen' THEN total_alloted ELSE 0 END) AS sqfgen_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfsc' THEN total_alloted ELSE 0 END) AS sqfsc_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfst' THEN total_alloted ELSE 0 END) AS sqfst_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfobca' THEN total_alloted ELSE 0 END) AS sqfobca_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqfobcb' THEN total_alloted ELSE 0 END) AS sqfobcb_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqopc' THEN total_alloted ELSE 0 END) AS sqopc_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqpwd' THEN total_alloted ELSE 0 END) AS sqpwd_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqogen' THEN total_alloted ELSE 0 END) AS sqogen_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqosc' THEN total_alloted ELSE 0 END) AS sqosc_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqost' THEN total_alloted ELSE 0 END) AS sqost_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqoobca' THEN total_alloted ELSE 0 END) AS sqoobca_admitted,
	SUM(CASE WHEN s_alloted_category = 'sqoobcb' THEN total_alloted ELSE 0 END) AS sqoobcb_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfpc' THEN total_alloted ELSE 0 END) AS dqfpc_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfgen' THEN total_alloted ELSE 0 END) AS dqfgen_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfsc' THEN total_alloted ELSE 0 END) AS dqfsc_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfst' THEN total_alloted ELSE 0 END) AS dqfst_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfobca' THEN total_alloted ELSE 0 END) AS dqfobca_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqfobcb' THEN total_alloted ELSE 0 END) AS dqfobcb_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqopc' THEN total_alloted ELSE 0 END) AS dqopc_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqogen' THEN total_alloted ELSE 0 END) AS dqogen_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqosc' THEN total_alloted ELSE 0 END) AS dqosc_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqost' THEN total_alloted ELSE 0 END) AS dqost_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqoobca' THEN total_alloted ELSE 0 END) AS dqoobca_admitted,
	SUM(CASE WHEN s_alloted_category = 'dqoobcb' THEN total_alloted ELSE 0 END) AS dqoobcb_admitted
	FROM (
    SELECT
        s_inst_code,
        s_trade_code,
        s_alloted_category,
        COUNT(*) AS total_alloted
    FROM jexpo_register_student
    WHERE s_admited_status = 1
    GROUP BY s_inst_code, s_trade_code, s_alloted_category
) AS subquery
GROUP BY s_inst_code, s_trade_code;";

        DB::select($temp_table);
        $seat_details_query    =    "SELECT
 i_name as institute_name,  master_table.sm_inst_code as inst_code ,
 t_name as trade_name, master_table.sm_trade_code ,
 master_table.m_tfw as mast_tfw,
(swap_master.m_tfw-master_table.m_tfw) as added_tfw,
swap_master.m_tfw as current_tfw,
COALESCE(adm_count_inst.tfw_admitted, 0) as admitted_tfw,
swap_master.m_tfw - COALESCE(adm_count_inst.tfw_admitted, 0) AS remain_tfw,
master_table.m_ews as mast_ews,
(swap_master.m_ews-master_table.m_ews) as added_ews,
swap_master.m_ews as current_ews,
COALESCE(adm_count_inst.ews_admitted, 0) as admitted_ews,
swap_master.m_ews - COALESCE(adm_count_inst.ews_admitted, 0) AS remain_ews,
master_table.m_sqfllq as mast_sqfllq,
(swap_master.m_sqfllq-master_table.m_sqfllq) as added_sqfllq,
swap_master.m_sqfllq as current_sqfllq,
COALESCE(adm_count_inst.sqfllq_admitted, 0) as admitted_sqfllq,
swap_master.m_sqfllq - COALESCE(adm_count_inst.sqfllq_admitted, 0) AS remain_sqfllq,
master_table.m_sqollq as mast_sqollq,
(swap_master.m_sqollq-master_table.m_sqollq) as added_sqollq,
swap_master.m_sqollq as current_sqollq,
COALESCE(adm_count_inst.sqollq_admitted, 0) as admitted_sqollq,
swap_master.m_sqollq - COALESCE(adm_count_inst.sqollq_admitted, 0) AS remain_sqollq,
master_table.m_dqollq as mast_dqollq,
(swap_master.m_dqollq-master_table.m_dqollq) as added_dqollq,
swap_master.m_dqollq as current_dqollq,
COALESCE(adm_count_inst.dqollq_admitted, 0) as admitted_dqollq,
swap_master.m_dqollq - COALESCE(adm_count_inst.dqollq_admitted, 0) AS remain_dqollq,
master_table.m_exs as mast_exs,
(swap_master.m_exs-master_table.m_exs) as added_exs,
swap_master.m_exs as current_exs,
COALESCE(adm_count_inst.exs_admitted, 0) as admitted_exs,
swap_master.m_exs - COALESCE(adm_count_inst.exs_admitted, 0) AS remain_exs,
master_table.m_sqfpc as mast_sqfpc,
(swap_master.m_sqfpc-master_table.m_sqfpc) as added_sqfpc,
swap_master.m_sqfpc as current_sqfpc,
COALESCE(adm_count_inst.sqfpc_admitted, 0) as admitted_sqfpc,
swap_master.m_sqfpc - COALESCE(adm_count_inst.sqfpc_admitted, 0) AS remain_sqfpc,
master_table.m_sqfgen as mast_sqfgen,
(swap_master.m_sqfgen-master_table.m_sqfgen) as added_sqfgen,
swap_master.m_sqfgen as current_sqfgen,
COALESCE(adm_count_inst.sqfgen_admitted, 0) as admitted_sqfgen,
swap_master.m_sqfgen - COALESCE(adm_count_inst.sqfgen_admitted, 0) AS remain_sqfgen,
master_table.m_sqfsc as mast_sqfsc,
(swap_master.m_sqfsc-master_table.m_sqfsc) as added_sqfsc,
swap_master.m_sqfsc as current_sqfsc,
COALESCE(adm_count_inst.sqfsc_admitted, 0) as admitted_sqfsc,
swap_master.m_sqfsc - COALESCE(adm_count_inst.sqfsc_admitted, 0) AS remain_sqfsc,
master_table.m_sqfst as mast_sqfst,
(swap_master.m_sqfst-master_table.m_sqfst) as added_sqfst,
swap_master.m_sqfst as current_sqfst,
COALESCE(adm_count_inst.sqfst_admitted, 0) as admitted_sqfst,
swap_master.m_sqfst - COALESCE(adm_count_inst.sqfst_admitted, 0) AS remain_sqfst,
master_table.m_sqfobca as mast_sqfobca,
(swap_master.m_sqfobca-master_table.m_sqfobca) as added_sqfobca,
swap_master.m_sqfobca as current_sqfobca,
COALESCE(adm_count_inst.sqfobca_admitted, 0) as admitted_sqfobca,
swap_master.m_sqfobca - COALESCE(adm_count_inst.sqfobca_admitted, 0) AS remain_sqfobca,
master_table.m_sqfobcb as mast_sqfobcb,
(swap_master.m_sqfobcb-master_table.m_sqfobcb) as added_sqfobcb,
swap_master.m_sqfobcb as current_sqfobcb,
COALESCE(adm_count_inst.sqfobcb_admitted, 0) as admitted_sqfobcb,
swap_master.m_sqfobcb - COALESCE(adm_count_inst.sqfobcb_admitted, 0) AS remain_sqfobcb,
master_table.m_sqopc as mast_sqopc,
(swap_master.m_sqopc-master_table.m_sqopc) as added_sqopc,
swap_master.m_sqopc as current_sqopc,
COALESCE(adm_count_inst.sqopc_admitted, 0) as admitted_sqopc,
swap_master.m_sqopc - COALESCE(adm_count_inst.sqopc_admitted, 0) AS remain_sqopc,
master_table.m_sqpwd as mast_sqpwd,
(swap_master.m_sqpwd-master_table.m_sqpwd) as added_sqpwd,
swap_master.m_sqpwd as current_sqpwd,
COALESCE(adm_count_inst.sqpwd_admitted, 0) as admitted_sqpwd,
swap_master.m_sqpwd - COALESCE(adm_count_inst.sqpwd_admitted, 0) AS remain_sqpwd,
master_table.m_sqogen as mast_sqogen,
(swap_master.m_sqogen-master_table.m_sqogen) as added_sqogen,
swap_master.m_sqogen as current_sqogen,
COALESCE(adm_count_inst.sqogen_admitted, 0) as admitted_sqogen,
swap_master.m_sqogen - COALESCE(adm_count_inst.sqogen_admitted, 0) AS remain_sqogen,
master_table.m_sqosc as mast_sqosc,
(swap_master.m_sqosc-master_table.m_sqosc) as added_sqosc,
swap_master.m_sqosc as current_sqosc,
COALESCE(adm_count_inst.sqosc_admitted, 0) as admitted_sqosc,
swap_master.m_sqosc - COALESCE(adm_count_inst.sqosc_admitted, 0) AS remain_sqosc,
master_table.m_sqost as mast_sqost,
(swap_master.m_sqost-master_table.m_sqost) as added_sqost,
swap_master.m_sqost as current_sqost,
COALESCE(adm_count_inst.sqost_admitted, 0) as admitted_sqost,
swap_master.m_sqost - COALESCE(adm_count_inst.sqost_admitted, 0) AS remain_sqost,
master_table.m_sqoobca as mast_sqoobca,
(swap_master.m_sqoobca-master_table.m_sqoobca) as added_sqoobca,
swap_master.m_sqoobca as current_sqoobca,
COALESCE(adm_count_inst.sqoobca_admitted, 0) as admitted_sqoobca,
swap_master.m_sqoobca - COALESCE(adm_count_inst.sqoobca_admitted, 0) AS remain_sqoobca,
master_table.m_sqoobcb as mast_sqoobcb,
(swap_master.m_sqoobcb-master_table.m_sqoobcb) as added_sqoobcb,
swap_master.m_sqoobcb as current_sqoobcb,
COALESCE(adm_count_inst.sqoobcb_admitted, 0) as admitted_sqoobcb,
swap_master.m_sqoobcb - COALESCE(adm_count_inst.sqoobcb_admitted, 0) AS remain_sqoobcb,
master_table.m_dqfpc as mast_dqfpc,
(swap_master.m_dqfpc-master_table.m_dqfpc) as added_dqfpc,
swap_master.m_dqfpc as current_dqfpc,
COALESCE(adm_count_inst.dqfpc_admitted, 0) as admitted_dqfpc,
swap_master.m_dqfpc - COALESCE(adm_count_inst.dqfpc_admitted, 0) AS remain_dqfpc,
master_table.m_dqfgen as mast_dqfgen,
(swap_master.m_dqfgen-master_table.m_dqfgen) as added_dqfgen,
swap_master.m_dqfgen as current_dqfgen,
COALESCE(adm_count_inst.dqfgen_admitted, 0) as admitted_dqfgen,
swap_master.m_dqfgen - COALESCE(adm_count_inst.dqfgen_admitted, 0) AS remain_dqfgen,
master_table.m_dqfsc as mast_dqfsc,
(swap_master.m_dqfsc-master_table.m_dqfsc) as added_dqfsc,
swap_master.m_dqfsc as current_dqfsc,
COALESCE(adm_count_inst.dqfsc_admitted, 0) as admitted_dqfsc,
swap_master.m_dqfsc - COALESCE(adm_count_inst.dqfsc_admitted, 0) AS remain_dqfsc,
master_table.m_dqfst as mast_dqfst,
(swap_master.m_dqfst-master_table.m_dqfst) as added_dqfst,
swap_master.m_dqfst as current_dqfst,
COALESCE(adm_count_inst.dqfst_admitted, 0) as admitted_dqfst,
swap_master.m_dqfst - COALESCE(adm_count_inst.dqfst_admitted, 0) AS remain_dqfst,
master_table.m_dqfobca as mast_dqfobca,
(swap_master.m_dqfobca-master_table.m_dqfobca) as added_dqfobca,
swap_master.m_dqfobca as current_dqfobca,
COALESCE(adm_count_inst.dqfobca_admitted, 0) as admitted_dqfobca,
swap_master.m_dqfobca - COALESCE(adm_count_inst.dqfobca_admitted, 0) AS remain_dqfobca,
master_table.m_dqfobcb as mast_dqfobcb,
(swap_master.m_dqfobcb-master_table.m_dqfobcb) as added_dqfobcb,
swap_master.m_dqfobcb as current_dqfobcb,
COALESCE(adm_count_inst.dqfobcb_admitted, 0) as admitted_dqfobcb,
swap_master.m_dqfobcb - COALESCE(adm_count_inst.dqfobcb_admitted, 0) AS remain_dqfobcb,
master_table.m_dqopc as mast_dqopc,
(swap_master.m_dqopc-master_table.m_dqopc) as added_dqopc,
swap_master.m_dqopc as current_dqopc,
COALESCE(adm_count_inst.dqopc_admitted, 0) as admitted_dqopc,
swap_master.m_dqopc - COALESCE(adm_count_inst.dqopc_admitted, 0) AS remain_dqopc,
master_table.m_dqogen as mast_dqogen,
(swap_master.m_dqogen-master_table.m_dqogen) as added_dqogen,
swap_master.m_dqogen as current_dqogen,
COALESCE(adm_count_inst.dqogen_admitted, 0) as admitted_dqogen,
swap_master.m_dqogen - COALESCE(adm_count_inst.dqogen_admitted, 0) AS remain_dqogen,
master_table.m_dqosc as mast_dqosc,
(swap_master.m_dqosc-master_table.m_dqosc) as added_dqosc,
swap_master.m_dqosc as current_dqosc,
COALESCE(adm_count_inst.dqosc_admitted, 0) as admitted_dqosc,
swap_master.m_dqosc - COALESCE(adm_count_inst.dqosc_admitted, 0) AS remain_dqosc,
master_table.m_dqost as mast_dqost,
(swap_master.m_dqost-master_table.m_dqost) as added_dqost,
swap_master.m_dqost as current_dqost,
COALESCE(adm_count_inst.dqost_admitted, 0) as admitted_dqost,
swap_master.m_dqost - COALESCE(adm_count_inst.dqost_admitted, 0) AS remain_dqost,
master_table.m_dqoobca as mast_dqoobca,
(swap_master.m_dqoobca-master_table.m_dqoobca) as added_dqoobca,
swap_master.m_dqoobca as current_dqoobca,
COALESCE(adm_count_inst.dqoobca_admitted, 0) as admitted_dqoobca,
swap_master.m_dqoobca - COALESCE(adm_count_inst.dqoobca_admitted, 0) AS remain_dqoobca,
master_table.m_dqoobcb as mast_dqoobcb,
(swap_master.m_dqoobcb-master_table.m_dqoobcb) as added_dqoobcb,
swap_master.m_dqoobcb as current_dqoobcb,
COALESCE(adm_count_inst.dqoobcb_admitted, 0) as admitted_dqoobcb,
swap_master.m_dqoobcb - COALESCE(adm_count_inst.dqoobcb_admitted, 0) AS remain_dqoobcb

from alloted_admitted_seat_master as swap_master
 join \"alloted_admitted_seat_master-2nd-round\" as \"master_table\" on master_table.sm_id=swap_master.sm_id
LEFT join admitted_counts as adm_count_inst on adm_count_inst.admitted_college=master_table.sm_inst_code and adm_count_inst.admitted_trade=master_table.sm_trade_code
 -- join admitted_counts as adm_count_trade on a
JOIN institute_master ON i_code =  master_table.sm_inst_code
 JOIN trade_master ON t_code =  master_table.sm_trade_code
 where i_type!= 'PVT'
order by i_name ASC, t_name ASC
-- group by master_table.sm_inst_code, master_table.sm_trade_code";

        $data    =    DB::select($seat_details_query);

        $cat_data    =    $this->get_category_arry();

        dd($data);
        return response()->json([
            'under_maintenance' => false,
            'message'           =>  null
        ]);
    }


    function get_category_arry($type_arr = [])
    {
        $counsCategory    =    counsCategory();
        $type_arr['category']    =    'TFW,SQOGEN,SQOSC,SQOST';
        /****
		$type value example :master,remain,  all: if want both

         *****/
        $type    =    isset($type_arr['type']) ? $type_arr['type'] : '';

        /****
		$category value example :TFW,EWS,....  all: if want all category
		if multiple then use | separator

         *****/
        $category    =    isset($type_arr['category']) ? $type_arr['category'] : '';
        //dd($counsCategory);

        $category_arr    =    explode(',', $category);
        $database_field    =    array();

        foreach ($counsCategory as $k => $cc) {
            if (($category == "all") || (in_array($k, $category_arr))) {
                $k = strtolower($k);
                $database_field[]    = "m_$k, $k , a_$k";
            }
        }
        return $database_field;
        //dd($database_field	);
    }

    function generate_seat_master()
    {
        $seat_details_query    = "SELECT
			i_type as \"INSTITUTE TYPE\", i_name as \"INSTITUTE NAME\",  sm_inst_code as \"INSTITUTE CODE\" ,
			t_name as \"TRADE NAME\", sm_trade_code \"TRADE CODE\" ,
			tfw , sqpwd,sqogen ,sqost, sqosc
			from alloted_admitted_seat_master
			JOIN institute_master ON i_code =  sm_inst_code
			JOIN trade_master ON t_code =  sm_trade_code
			where i_type!= 'PVT'

    order by i_type, i_name ASC, t_name ASC ";
        $data    =    DB::select($seat_details_query);

        dd($data);
    }
}
