<?php

namespace AppBundle\Utils;

use Ob\HighchartsBundle\Highcharts\Highchart;

class GraphCreator
{
    public function createLineGraph(array $data, string $id, string $title, string $horizontalTitle, string $verticalTitle): Highchart
    {
        $ob = new Highchart();
        $ob->chart->renderTo($id);  // The #id of the div where to render the chart
        $ob->title->text($title);
        $ob->xAxis->title(array('text'  => $horizontalTitle));
        $ob->yAxis->title(array('text'  => $verticalTitle));
        $ob->series($data);
        return $ob;
    }

    public function createPieGraph(array $data, string $id, string $title, string $hover): Highchart
    {
        $ob = new Highchart();
        $ob->chart->renderTo($id);
        $ob->title->text($title);
        $ob->plotOptions->pie(array(
            'allowPointSelect'  => true,
            'cursor'    => 'pointer',
            'dataLabels'    => array('enabled' => false),
            'showInLegend'  => true
        ));
        $ob->series(array(array('type' => 'pie','name' => $hover, 'data' => $data)));
        return $ob;
    }
}