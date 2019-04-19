<?

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//Vue.js plugIn
//$APPLICATION->AddHeadScript('/local/components/itlogic/stage_counters_report/templates/.default/vue.js');
$APPLICATION->AddHeadScript('/local/components/itlogic/stage_counters_report/templates/.default/vue.min.js');
//\Bitrix\Main\UI\Extension::load("ui.vue"); // doesn't work here!
?>
    <section class="custom-stage-counters" id="filters">

        <h2 class="report-title">Отчет по счетчикам стадий сделок с
            <span class="date-span">{{dateFrom.split('-').reverse().join('.')}} </span>
            по
            <span class="date-span">{{dateTo.split('-').reverse().join('.')}}</span>
        </h2>



    <div class="counter-filters" >

        <table>
            <tr>
                <td>
                    <label for="deal_category">Выберите направление:</label>
                </td>
                <td>
                    <select name='deal_category' v-model="categoryFilter" @change="getStatisticsByFilter()">
                        <option v-for="category in categories" v-bind:value="category.ID">{{category.NAME}}</option>
                    </select>
                </td>
                <td>
                    <label for="deal_category">Выберите ответственного:</label>
                </td>
                <td>
                    <select name='deal_category' v-model="assigned_byFilter" @change="getStatisticsByFilter()">
                        <option v-for="assigned in assignedList" v-bind:value="assigned.ID">{{assigned.NAME}}</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="deal_category">Выберите текущую стадию:</label>
                </td>
                <td>
                    <select name='deal_category' v-model="current_stage_idFilter" @change="getStatisticsByFilter()">
                        <option v-for="stage in stagesList" v-bind:value="stage.ID">{{stage.NAME}}</option>
                    </select>
                </td>
                <td>
                    <label for="only_opened">Только сделки в работе</label>
                </td>
                <td>
                    <input name="only_opened" v-model="onlyOpenedDeals" type="checkbox" @change="getStatisticsByFilter()" id="only_opened">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="date_from">Дата с:</label>
                </td>
                <td>
                    <input name="date_from" v-model="dateFrom" type="date" @change="getStatisticsByFilter()">
                </td>
                <td>
                    <label for="date_to">Дата по:</label>
                </td>
                <td>
                    <input name="date_to" v-model="dateTo" type="date" @change="getStatisticsByFilter()">
                </td>
            </tr>
        </table>

    </div>
        <div class="table-wrapper-x-scroll">
            <table class="custom-table">
                <thead>
                <tr>
                    <th>№</th>
                    <th>Название сделки</th>
                    <th v-for="value in dealStages">{{value.STAGE_NAME}} ({{value.STAGE_ID}})</th>
                </tr>
                </thead>
                <tbody>

                <tr v-if="dealsData.length === 0">
                    <td v-bind:colspan="2 + dealStages.length" class="zero-deals">{{dealsData.length}} сделок по текущему фильтру!</td>
                </tr>
                <template v-else>
                    <tr v-for="(deal,key) in dealsData" v-bind:title="deal.ASSIGNED_NAME">
                        <td>{{key + 1}}</td>
                        <td class="table-deal-name"><a v-bind:href="deal.HREF">{{deal.TITLE}}</a></td>
                        <td v-for="stage in deal.HISTORY"
                            v-bind:class="{ currentStage: stage.IS_CURRENT_STAGE && !stage.OVER_TIME, deal_overtime_10: stage.OVER_TIME === 1,deal_overtime_30: stage.OVER_TIME === 2}">
                            <!--{{stage.NAME}} - -->{{stage.PERIOD}}
                        </td>

                    </tr>
                    <tr class='whole-statistics'><td>Всего:</td><td >{{dealsData.length}} сделок</td><td v-bind:colspan="dealStages.length"></td></tr>
                </template>

                </tbody>
            </table>
        </div>


</section>
    <script src="/local/components/itlogic/stage_counters_report/templates/.default/vueJsFunctions.js"></script>
<?
//echo '<pre>';
//print_r($arResult);
//echo '</pre>';