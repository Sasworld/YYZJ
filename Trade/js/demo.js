// 选择对话框
var truthBeTold = window.confirm("此单手续费：" + handling_fee + ",扣除后剩余排单币：" + (pdcoin_amount - handling_fee));
if (truthBeTold) {
	close_pop(3);
	insert_paidan(pd_money);
} else{
	$('#text_pd_money').val("")
	close_pop(3);
}