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



/** Highlight active menu */
$('.sidebar-menu a').each(function () {
// window.location.protocol = http или https далее конкатенация . ‘//’ .  //далее хост window.location.host + и window.location.pathname
    var location = window.location.protocol + '//' + window.location.host + window.location.pathname;
    var link = this.href;
    if (link === location){
        $(this).parent().addClass('active');
        $(this).closest('.treeview').addClass('active');
    }
});


/** KCFinder  */

$( '#editor1' ).ckeditor();

/** Filter reset */
$('#reset-filter').click(function () {
    $('#filter input[type=radio]').prop('checked', false);
    return false;
});

/** You must choose category */
$('#add').on('submit', function () {
    if (!isNumeric($('#parent_id').val())) {
        alert('Выберите категорию');
        return false;
    }
});

/** You must choose category */
$('#addattrs').on('submit', function () {
    if (!isNumeric($('#category_id').val())) {
        alert('Выберите группу');
        return false;
    }
});


function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}
