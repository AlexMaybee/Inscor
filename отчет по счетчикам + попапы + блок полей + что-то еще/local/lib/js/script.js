BX.ready(function() {

    $(document).on("DOMNodeInserted", function (event) {
        var reports = document.querySelectorAll('ul.crm-p-s-f-checkbox-items-list');
        if (reports.length > 0) {

            var inputContact = document.querySelectorAll('input#current_config_contact'),
                inputCompany = document.querySelectorAll('input#current_config_company'),
                inputDeal = document.querySelectorAll('input#current_config_deal');

            if(inputContact.length > 0) {
                inputContact[0].checked = false;
            }

            if(inputCompany.length > 0) {
                inputCompany[0].checked = false;
            }

            if(inputDeal.length > 0) {
                inputDeal[0].checked = false;
            }
        }
    })

})