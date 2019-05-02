$('document').ready(function () {

    //console.log('НОВЫЙ ФАЙЛ?!!');

    BX.ready(function() {
        var href = window.location.href,//получение адресной строки
            matches,deal_id;

        if(matches = href.match(/\/crm\/deal\/details\/([\d]+)\//i)){
            deal_id = matches[1]; //id сделки

            //разноцветные счетчики стадий в верхней панели
            if(deal_id > 0) {

                //30.04.2019 - старт переделки
                let countersClass = new DealCounters(deal_id)
            }

        }

    });

});



class DealCounters{
    constructor(dealId){

        if(dealId > 0){
            this.dealId = dealId;
            this.insertCountersNew(this.dealId);
        }
    }

    insertCountersNew(dealId){
     //   console.log(dealId, 'Функция запроса счетчиков NEW!');

        var self = this;

        BX.ajax({
            method: "POST",
            url: '/local/ajax/ajax_handler.php',
            data: {'DEAL_ID':dealId,'ACTION':'GIVE_ME_STAGE_COUNTERS_NEW'},
            dataType: "json",
            onsuccess: function (data) {

                //console.log(data);
                if(!data.result) console.log(data.error);
                else {
                    data.result.forEach(function (value,index) {
                       // console.log(index,value.NAME,value.PERIOD,value.IS_CURRENT_STAGE);
                        if(value.PERIOD != 0){

                            //обрезаем название стадии
                            (value.NAME.length > 6) ? value.SHORT_NAME = value.NAME.substr(0,6) + '...' : value.SHORT_NAME = value.NAME;

                            //обрезаем счетчик
                            (value.PERIOD.length > 10) ? value.SHORT_PERIOD = value.PERIOD.substr(0,9) + '...' : value.SHORT_PERIOD = value.PERIOD;

                            if(value.IS_CURRENT_STAGE > 0) self.currentStageIndicator(value);
                            else self.goneStagesIndicator(value);
                        }
                    })
                }
            }
        });

    }

    currentStageIndicator(counterData){
        var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
        var css = 'background-color: #21ed69;';

        //цвет индикатора console.log(typeof counterData.OVER_TIME);
        if(typeof counterData.OVER_TIME !== 'undefined'){
            if(counterData.OVER_TIME == 1) css = ' background-color: darkorange; color: #fff;';
            if(counterData.OVER_TIME == 2) css = ' background-color: darkred; color: #fff;';
        }

        if(mdiv != null){
            var bp = document.createElement('span');
            bp.className = 'current-stage-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
            bp.innerHTML = counterData.SHORT_NAME + ': ' + counterData.SHORT_PERIOD;
            bp.title = 'На стадии ' + counterData.NAME + ' сделка находится суммарно ' + counterData.PERIOD;
            bp.style.cssText = 'display: inline-block!important; ' + css;
            mdiv.before(bp);
        }
    }

    goneStagesIndicator(counterData){
        var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
        if(mdiv != null){
            var bp = document.createElement('span');
            bp.className = 'gone-stage-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
            bp.innerHTML = counterData.SHORT_NAME + ': ' + counterData.SHORT_PERIOD;
            bp.title = 'На стадии ' + counterData.NAME + ' сделка находилась ' + counterData.PERIOD;
            bp.style.cssText = 'display: inline-block!important; background-color: #b3fff1; ';
            mdiv.before(bp);
        }
    }


}
