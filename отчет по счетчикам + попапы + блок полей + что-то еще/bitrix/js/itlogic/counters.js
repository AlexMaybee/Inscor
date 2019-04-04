$('document').ready(function () {

    BX.ready(function() {
        var href = window.location.href,//получение адресной строки
            matches,deal_id;

        if(matches = href.match(/\/crm\/deal\/details\/([\d]+)\//i)){
            deal_id = matches[1]; //id сделки

          // console.log(deal_id);

            //разноцветные счетчики стадий в верхней панели
            if(deal_id > 0) {
                insertCountersToDeal(deal_id);
            }

        }

    });

});



function insertCountersToDeal(deal_id) {

    BX.ajax({
        method: "POST",
        url: '/local/ajax/ajax_handler.php',
        data: {'DEAL_ID':deal_id,'ACTION':'GIVE_ME_STAGE_COUNTERS'},
        dataType: "json",
        onsuccess: function (data) {

          //  console.log(data);
           // console.log(data.COUNTERS.CUR_STAGE);

            if(data.COUNTERS !== false) {

                var goneStages = data.COUNTERS.OTHER_STAGES;
                var curStageData = data.COUNTERS.CUR_STAGE;


                if(goneStages.length > 0){
                    $.each(goneStages,function (index,field) {
                        var shortName = field.NAME;
                        var title;
                        if(goneStages.length > 1){
                            shortName = field.NAME.substr(0,5) + '...';
                            //if(field.NAME.length > 7) field.NAME = shortName;
                        }
                        title = 'На стадии ' + field.NAME + ' сделка находилась ' + field.PERIOD;
                        addInProcessWaitingPriceIndicator(shortName + ' : ' + field.PERIOD, title);
                    })
                }
             //   console.log(typeof curStageData.NAME);
                if(typeof(curStageData.NAME) !== 'undefined'){

                    var shortName = curStageData.NAME;
                    if(shortName.length > 6) shortName = curStageData.NAME.substr(0,6) + '...';
                    var title = 'Текущая стадия - ' + curStageData.NAME + ' : ' + curStageData.COUNTER;
                    addAgreedIndicator(shortName + ' : ' + curStageData.COUNTER, title);
                    //console.log('Тек сделка есть!');
                }

               // console.log(data.MESSAGE)


                //Пытался анимировать индикаторы при помощи js'a - fail пока
              /*  var indicators = document.getElementsByClassName('task-indicator');
               // console.log(indicators)
                $.each(indicators,function (index,indicator) {


                    $(indicator).mouseover(function () {
                        console.log(this.innerHTML);
                        if($(this).css('max-width') !== 'none'){
                            $(this).css({'max-width':'none','overflow':'visible'});
                        }
                    });
                    $(indicator).mouseout(function () {
                        if($(this).css('max-width') === 'none' && $(this).css('overflow') === 'visible'){
                            $(this).css({'max-width':'100px','overflow':'hidden'});
                            $(this).text($(this).text().substr(0,5));

                        }
                    });

                });*/

            }
            else console.log(data.MESSAGE)
        }
    });

}


//   ### !!!Счетчики-Индикаторы!!! ####

//Добавление индикатора "Не согласовано"
/*function addUnAgreedIndicator(name) {
    var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
    if(mdiv != null){
        var bp = document.createElement('span');
        bp.className = 'dis-agreed-spec-price-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
        bp.innerHTML = name;
        bp.title = name;
        bp.style.cssText = 'display: inline-block!important; background-color: #FF4500; color: #fff;'; // pointer-events: none
        mdiv.before(bp);
    }
}*/

//Добавление индикатора "Согласовано" - THIS!
function addAgreedIndicator(name,title) {
    var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
    if(mdiv != null){
        var bp = document.createElement('span');
        bp.className = 'agreed-spec-price-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
        bp.innerHTML = name;
        bp.title = title;
        bp.style.cssText = 'display: inline-block!important;';// pointer-events: none;
        mdiv.before(bp);
    }
}

//Добавление индикатора "На просчете администратором"
/*function addInProcessCalculatePriceIndicator(name) {
    var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
    if(mdiv != null){
        var bp = document.createElement('span');
        bp.className = 'in-process-spec-price-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
        bp.innerHTML = name;
        bp.title = name;
        bp.style.cssText = 'display: inline-block!important; background-color: #FFA500; '; //pointer-events: none;
        mdiv.before(bp);
    }
}*/

//Добавление индикатора "На согласовании с клиентом" - THIS!
function addInProcessWaitingPriceIndicator(name,title) {
    var mdiv = $('.pagetitle-container.pagetitle-align-right-container .crm-entity-actions-container');
    if(mdiv != null){
        var bp = document.createElement('span');
        bp.className = 'on-agreement-by-client-spec-price-indicator task-view-button bp_start webform-small-button webform-small-button-accept task-indicator';
        bp.innerHTML = name;
        bp.title = title;
        bp.style.cssText = 'display: inline-block!important; background-color: #AFEEEE; '; // pointer-events: none; // max-width: 100px; overflow: hidden;
        mdiv.before(bp);
    }
}

