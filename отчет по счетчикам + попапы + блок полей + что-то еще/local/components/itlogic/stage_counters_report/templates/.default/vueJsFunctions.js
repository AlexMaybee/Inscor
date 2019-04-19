var filters = new Vue({
    el: '#filters',
    data: {
        categoryFilter: 'test 123',
        categories: '',
        assigned_byFilter: 0,
        assignedList: '',
        stagesList: '',
        current_stage_idFilter: '',
        dateFrom: '',
        dateTo:'',
        dealStages: '',
        onlyOpenedDeals: true,
        errorText: false,
        dealsData: true,

    },
    methods: {

        getCategories: function () {
            let self = this;
            BX.ajax({
                method: "POST",
                url: '/custom_reports/stage_counters/ajax/handler.php',
                data: {'ACTION':'GIVE_ME_CATEGORIES_FOR_SELECT'},
                dataType: "json",
                onsuccess: function (response) {

                    self.categories = response;
                    self.categoryFilter = self.categories[0].ID; //присваиваем значение selected при загрузке, чтобы можно было сразу запустить загрузку таблицы
                   // console.log(self.categories);


                    //Запрос всех стадий для селекта, т.к. уже присвоен ID категории в поле фильтра
                    self.getStagesList(self.categoryFilter);


                    var date = new Date();
                    var month, day;
                    if(date.getMonth() < 10) month = '0' + (date.getMonth()+1);
                    else month = date.getMonth()+1;

                    if(date.getDate() < 10) day = '0' + date.getDate();
                    else day = date.getDate();

                    self.dateFrom = date.getFullYear() + '-' + month + '-' + day;
                    self.dateTo = date.getFullYear() + '-' + month + '-' + day;

                    //Вызываем функцию
                    self.getStatisticsByFilter(); //после присвоения значений фильтрам загружаем данные в таблицу с фильтрами по умолчанию.
                }
            });
        },

        getStatisticsByFilter: function(){
            let self = this;
            BX.ajax({
                method: "POST",
                url: '/custom_reports/stage_counters/ajax/handler.php',
                data: {
                    'ACTION':'GIVE_ME_STATISTICS_BY_CATEGORY_ID',
                    'ASSIGNED_BY_ID': this.assigned_byFilter,
                    'DATE_START': this.dateFrom,
                    'DATE_FINISH': this.dateTo,
                    'ONLY_OPENED_DEALS': this.onlyOpenedDeals, //в php почему-то передает строку 'true' / 'false'
                    'CATEGORY_ID':this.categoryFilter,
                    'STAGE_ID':this.current_stage_idFilter
                },
                dataType: "json",
                onsuccess: function (response) {

                   // console.log(response);
                   // console.log(self.categoryFilter,self.assigned_byFilter,self.onlyOpenedDeals,self.dateFrom,self.dateTo,self.current_stage_idFilter);

                    //вывод стадий в шапку <th>
                    if(response.stages != false) self.dealStages = response.stages;
                    /*if(response.statistics != false)*/ self.dealsData = response.statistics;

                }
            });
        },

        getAssignedList: function () {
            let self = this;
            BX.ajax({
                method: "POST",
                url: '/custom_reports/stage_counters/ajax/handler.php',
                data: {'ACTION':'GIVE_ME_ASSIGNED_LIST_FOR_SELECT'},
                dataType: "json",
                onsuccess: function (response) {
                //    console.log(response)
                    self.assignedList = response;
                    self.assigned_byFilter = self.assignedList[0].ID; //присваиваем значение selected при загрузке, чтобы можно было сразу запустить загрузку таблицы

                }
            });
        },
        getStagesList: function () {

           // console.log('тест2',this.categoryFilter);

            let self = this;
            BX.ajax({
                method: "POST",
                url: '/custom_reports/stage_counters/ajax/handler.php',
                data: {
                    'ACTION':'GIVE_ME_STAGES_LIST_FOR_SELECT',
                    'CATEGORY_ID':this.categoryFilter
                },
                dataType: "json",
                onsuccess: function (response) {
                  //  console.log(response)
                     self.stagesList = response;
                     self.current_stage_idFilter = self.stagesList[0].ID; //присваиваем значение selected при загрузке, чтобы можно было сразу запустить загрузку таблицы

                }
            });
        },
    },

    mounted: function () {
        this.getCategories();
        this.getAssignedList();
        //this.getStagesList(this.categoryFilter); // здесь не срабатывает, т.к. еще не присвоено значение полю categoryFilter

    },
});