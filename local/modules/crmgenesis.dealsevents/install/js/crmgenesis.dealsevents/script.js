BX.ready(function() {
    let obj = new CustomDealsEvents();
});

class CustomDealsEvents {
    constructor(){
        this.urlStr = window.location.href;
        this.ajaxUrl = '/local/modules/crmgenesis.dealsevents/ajax.php';
        this.waitDealStageChange();
    }

    waitDealStageChange(){
        BX.addCustomEvent("onAjaxSuccess", function(data, config) {
            // console.log('data',data);
            // console.log('config',config);
            if(typeof config.data !== 'undefined' && typeof(config.data) === 'string'){

                let data_massive = config.data.split('&'),
                    values_massive = {},
                    arr;

                $.each(data_massive,function (index,values) {
                    var arr = values.split('=');
                    values_massive[arr[0]] = arr[1];
                });

                //данные собраны, теперь необходимо запустить проверки для запуска ф-ции с отображением кнопок
                if(typeof(values_massive.ACTION) !== 'undefined' && values_massive.ACTION == 'SAVE_PROGRESS'){
                    if(typeof(values_massive.TYPE) !== 'undefined' && values_massive.TYPE == 'DEAL'){
                        if(typeof(values_massive.VALUE) !== 'undefined'){ //&& values_massive.VALUE == '11'
                            if(typeof(values_massive.ID) !== 'undefined' && values_massive.ID > 0 ){
                                console.log(values_massive.ID);
                            }
                        }
                    }
                }
            }

        });
    }

}