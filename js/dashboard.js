/**dashboard**/
var charts = null;
function refreshPage() {
    window.location.href = window.location.href;
}
var TEMS = {
    chart: {
        chartObj: null,
        chartOptions: {},
        defaultChart: 'column3d',
        chartTypes: {
            area2d: 'charts/FCF_Area2D.swf',
            bar2d: 'charts/FCF_Bar2D.swf',
            column2d: 'charts/FCF_Column2D.swf',
            column3d: 'charts/FCF_Column3D.swf',
            line2d: 'charts/FCF_Line.swf',
            msarea2d: 'charts/FCF_MSArea2D.swf',
            msbar2d: 'charts/FCF_MSBar2D.swf',
            mscolum2dlinedy: 'charts/FCF_MSColumn2DLineDY.swf',
            msline2d: 'charts2/FCF_MSLine.swf',
            multicolumn3d: 'charts/FCF_MSColumn3D.swf',
            pie2d: 'charts/FCF_Pie2D.swf',
            pie3d: 'charts/FCF_Pie3D.swf'
        },
        init: function(target,options) {
            var self = this,height=400,width = $('#'+target).parent().width(),
            chartClip = self.chartTypes[self.defaultChart];
            self.chartOptions = options;
            if(typeof(options) == 'object') {
                if(typeof(options.chart_type) == 'undefined') {
                    chartClip = self.chartTypes[self.defaultChart];
                } else {
                    chartClip = self.chartTypes[options.chart_type];
                    self.defaultChart = options.chart_type;
                }
                if(typeof(options.height) != 'undefined') {
                    height = options.height;
                }
                if(typeof(options.width) != 'undefined') {
                    width = options.width;
                }
            }
            var chartID = typeof(options.id) != 'undefined' ? options.id : 'chart1';

            if(typeof(FusionCharts) != 'undefined') {
                var chart = new FusionCharts(chartClip, chartID, width, height);
                chart.params['wMode'] = 'transparent';
                self.chartObj = chart;
                return chart;
            }
            return false;
        },
        loadData: function(chart,options,targetID) {
            if(typeof(chart) != 'object') {
                return false;
            }
            if ($('#'+targetID).find('.chart').children().length > 0) {
                return;
            }
            $.ajax({
                url: 'temschart_ajax.php',
                data: options,
                dataType:'json',
                success:function(result) {

                    if(typeof(result) != 'undefined' && result) {
                        if(result != 'session_expired') {
                            chart.setDataXML(result.xmlstring);
                            //console.log(targetID);
                            chart.render(targetID);
                            //$('#'+targetID).parent().show();
                        } else {
                            refreshPage();
                        }
                    } else {
                        //$('#'+targetID).parent().hide();
                        $('#'+targetID).html('<div class="alert alert-error">No readings recorded.</div>');
                    }

                },
                error: function() {
                    //$('#'+targetID).parent().hide();
                }
            });
            return false;
        },
        loadChart: function(targetID,args) {
            if(!document.getElementById(targetID)) {
                return;
            }
            var self = this;
            var options = {
                chart_type : 'multicolumn3d'
            };
            if(typeof(args) == 'object') {
                for(var x in args) {
                    options[x] = args[x];
                }
            }

            var chartOptions = {};
            $.extend(chartOptions,options);

            if(typeof(chartOptions['id'] != 'undefined')) {
                chartOptions['id']  =   options['chartId'];
            }
            var chart = self.init(targetID,chartOptions);

            if(chart) {
                self.loadData(chart,options,targetID);
            } else {
                console.log('fusion chart load failed');
            }
            return false;
        }
    },
};
$(function() {

    charts = {
        /*
        aresLevel: {
            chartId: 'eqoxygenlevel',
            chart_type: 'msline2d',
            containerId: 'oxygen_level',
            id: 78,
            type: 'oxygen_level',
            title: 'Cryo Room (Stemcell) O2 Level'
        },
        */
        pendingWork: {
            chartId: 'eqpending',
            containerId: 'pending_forecast',
            type: 'pending_work',
            title: ''//'Pending Work - Forecast'
        },
        totalWork: {
            chartId: 'eqtotalpending',
            containerId: 'allpending_forecast',
            width: $('#pending_work_distribution').width(),
            type: 'total_pending',
            title: ''//'All Pending Work - Forecast'
        },
        pendingDistribution: {
            chartId: 'eqpendingdst',
            chart_type: 'pie3d',
            containerId: 'pending_distribution',
            type: 'pending_distribution',
            title: ''
        },
        pendingDistributionW1: {
            chartId: 'eqpendingdstw1',
            chart_type: 'pie3d',
            containerId: 'pendingdst_week1',
            type: 'pending_distribution',
            period: 'week_1',
            title: '< 1 Week'
        },
        pendingDistributionW2: {
            chartId: 'eqpendingdstw2',
            chart_type: 'pie3d',
            containerId: 'pendingdst_week2',
            type: 'pending_distribution',
            period: 'week_2',
            title: '1 - 2 Weeks'
        },
        pendingDistributionW3: {
            chartId: 'eqpendingdstw3',
            chart_type: 'pie3d',
            containerId: 'pendingdst_week3',
            type: 'pending_distribution',
            period: 'week_3',
            title: '2 - 4 Weeks'
        },
        pendingDistributionM1: {
            chartId: 'eqpendingdstm1',
            chart_type: 'pie3d',
            containerId: 'pendingdst_month1',
            type: 'pending_distribution',
            period: 'month_1',
            title: '1 - 2 Months'
        },
        pendingDistributionM2: {
            chartId: 'eqpendingdstm2',
            chart_type: 'pie3d',
            containerId: 'pendingdst_month2',
            type: 'pending_distribution',
            period: 'month_2',
            title: '2 - 4 Months'
        },
        pendingDistributionM3: {
            chartId: 'eqpendingdstm3',
            chart_type: 'pie3d',
            containerId: 'pendingdst_month3',
            type: 'pending_distribution',
            period: 'month_3',
            title: '> 6 Months'
        },
        allPendingDistribution: {
            chartId: 'eqpendingdstall',
            chart_type: 'pie3d',
            containerId: 'allpending_distribution',
            type: 'total_pending_distribution',
            title: ''
        },
        allPendingDistributionW1: {
            chartId: 'eqpendingdstallw1',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_week1',
            type: 'total_pending_distribution',
            period: 'week_1',
            title: '< 1 Week'
        },
        allPendingDistributionW2: {
            chartId: 'eqpendingdstallw2',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_week2',
            type: 'total_pending_distribution',
            period: 'week_2',
            title: '1 - 2 Weeks'
        },
        allPendingDistributionW3: {
            chartId: 'eqpendingdstallw3',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_week3',
            type: 'total_pending_distribution',
            period: 'week_3',
            title: '2 - 4 Weeks'
        },
        allPendingDistributionM1: {
            chartId: 'eqpendingdstallm1',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_month1',
            type: 'total_pending_distribution',
            period: 'month_1',
            title: '1 - 2 Months'
        },
        allPendingDistributionM2: {
            chartId: 'eqpendingdstallm2',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_month2',
            type: 'total_pending_distribution',
            period: 'month_2',
            title: '2 - 4 Months'
        },
        allPendingDistributionM3: {
            chartId: 'eqpendingdstallm3',
            chart_type: 'pie3d',
            containerId: 'allpendingdst_month3',
            type: 'total_pending_distribution',
            period: 'month_3',
            title: '> 6 Months'
        },
        upcomingWork: {
            chartId: 'equpcoming',
            containerId: 'upcoming_forecast',
            type: 'upcoming_work',
            title: ''//'Upcoming Work - Forecast'
        },
        totalUpcoming: {
            chartId: 'eqtotalupcoming',
            containerId: 'allupcoming_forecast',
            type: 'total_upcoming',
            title: '' //'All Upcoming Work - Forecast'
        },
        upcomingDistribution: {
            chartId: 'equpcomingdst',
            chart_type: 'pie3d',
            containerId: 'upcoming_distribution',
            type: 'upcoming_distribution',
            title: ''
        },
        upcomingDistributionW1: {
            chartId: 'equpcomingdstw1',
            chart_type: 'pie3d',
            containerId: 'upcomingdst_week1',
            type: 'upcoming_distribution',
            period: 'week_1',
            title: '< 1 Week'
        },
        upcomingDistributionW2: {
            chartId: 'equpcomingdstw2',
            chart_type: 'pie3d',
            containerId: 'upcomingdst_week2',
            type: 'upcoming_distribution',
            period: 'week_2',
            title: '1 - 2 Weeks'
        },
        upcomingDistributionW3: {
            chartId: 'equpcomingdstw3',
            chart_type: 'pie3d',
            containerId: 'upcomingdst_week3',
            type: 'upcoming_distribution',
            period: 'week_3',
            title: '2 - 4 Weeks'
        },
        upcomingDistributionM1: {
            chartId: 'equpcomingdstm1',
            chart_type: 'pie3d',
            containerId: 'upcomingdst_month1',
            type: 'upcoming_distribution',
            period: 'month_1',
            title: '1 - 2 Months'
        },
        upcomingDistributionM2: {
            chartId: 'equpcomingdstm2',
            chart_type: 'pie3d',
            containerId: 'upcomingdst_month2',
            type: 'upcoming_distribution',
            period: 'month_2',
            title: '2 - 4 Months'
        },
        allUpcomingDistribution: {
            chartId: 'equpcomingdstall',
            chart_type: 'pie3d',
            containerId: 'allupcoming_distribution',
            type: 'total_upcoming_distribution',
            title: ''
        },
        allUpcomingDistributionW1: {
            chartId: 'equpcomingdstallw1',
            chart_type: 'pie3d',
            containerId: 'allupcomingdst_week1',
            type: 'total_upcoming_distribution',
            period: 'week_1',
            title: '< 1 Week'
        },
        allUpcomingDistributionW2: {
            chartId: 'equpcomingdstallw2',
            chart_type: 'pie3d',
            containerId: 'allupcomingdst_week2',
            type: 'total_upcoming_distribution',
            period: 'week_2',
            title: '1 - 2 Weeks'
        },
        allUpcomingDistributionW3: {
            chartId: 'equpcomingdstallw3',
            chart_type: 'pie3d',
            containerId: 'allupcomingdst_week3',
            type: 'total_upcoming_distribution',
            period: 'week_3',
            title: '2 - 4 Weeks'
        },
        allUpcomingDistributionM1: {
            chartId: 'equpcomingdstallm1',
            chart_type: 'pie3d',
            containerId: 'allupcomingdst_month1',
            type: 'total_upcoming_distribution',
            period: 'month_1',
            title: '1 - 2 Months'
        },
        allUpcomingDistributionM2: {
            chartId: 'equpcomingdstallm2',
            chart_type: 'pie3d',
            containerId: 'allupcomingdst_month2',
            type: 'total_upcoming_distribution',
            period: 'month_2',
            title: '2 - 4 Months'
        }
    };

    renderCharts();
    $(window).resize(function() {
        renderCharts();
    });

    function calculatePercent(value, total) {
        return (value/100) * total;
    }

    function isInt(n) {
        return typeof n === 'number' && parseFloat(n) == parseInt(n, 10) && !isNaN(n);
    }
    function renderCharts() {
        for(var c in charts) {
            var opt = {};
            for(var i in charts[c]) {
                var height =0,targetWidth = $('#' + charts[c]['containerId']).width();
                if ($('#' + charts[c]['containerId']).closest('.subtabs').length > 0) {
                    targetWidth = $('#' + charts[c]['containerId']).closest('.subtabs').width();
                    //console.log(targetWidth, 'subtabs width');
                    if(!targetWidth) {
                        targetWidth = $('#' + charts[c]['containerId']).closest('.subtabs').parent().width();
                        targetWidth = targetWidth/100 * window.innerWidth;
                    }
                } else {
                    targetWidth = $('#' + charts[c]['containerId']).parent().width();
                    if(!isInt(targetWidth)) {
                        //console.log($('#' + charts[c]['containerId']).parent(), charts[c]['containerId'] + ' PARENT');
                        var parent = $('#' + charts[c]['containerId']).parent();
                        //console.log(parent.width()/100 * window.innerWidth, 'parent.width()/100 * window.innerWidth');
                        targetWidth = parent.width()/100 * window.innerWidth;
                    }
                }
                if(targetWidth) {
                    height   =   calculatePercent(60, targetWidth);
                } else {
                    targetWidth = '100%';
                    height = 400;
                }
                opt['height']   =   height;
                opt['width']    =   targetWidth;
                opt[i] = charts[c][i];
            }
            opt.url = 'workorder.php';
            TEMS.chart.loadChart(opt.containerId,opt);
        }
    }
    $('.subtabs li').click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        var me = $(this);
        var lnk = $(this).find('a');

        if(lnk.length == 0) {
            return false;
        }

        var targetId = lnk.attr('href').substr(1);
        var chartContainer = $('#'+targetId).find('.chart');

        if(chartContainer.length > 0) {
            if(chartContainer.children().length == 0) {
                var chartId = chartContainer.attr('id');
                var obj = null;

                for (var i in charts) {
                    if(charts[i].containerId == chartId) {
                        obj = charts[i];
                        break;
                    }
                }
                if (obj) {
                    var opt = {};
                    for(var i in obj) {
                        var targetWidth = $('#' + obj['containerId']).width();
                        //console.log(targetWidth, 'targetWidth');
                        if (!targetWidth) {
                            targetWidth = me.parent().parent().width();
                            //console.log(targetWidth, 'subtab width');
                        }
                        opt['height']   =   calculatePercent(60, targetWidth);
                        opt['width']    =   targetWidth;
                        opt[i] = obj[i];
                    }
                    opt.url = 'workorder.php';
                    return TEMS.chart.loadChart(opt.containerId,opt);
                }
            }
        }
        return false;
    });
});
