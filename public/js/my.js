/** Подтверждение удаления заказа **/
$('.delete').click(function () {
    var res = confirm('Approve action');
    if(!res) return false;
});

/** Edit order **/
$('.redact').click(function () {
    var red = confirm('Edit only comment');
    return false;
});

/** Approve delete order from database **/
$('.deletebd').click(function () {
    var res = confirm('Approve action');
    if(res) {
        var res2 = confirm('Order will be delete from Database!');
        if(!res2) return false;
    }
    if(!res) return false;
});
