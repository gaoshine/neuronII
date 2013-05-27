function checkForm() {
	try {
		if ($.trim($('#person').val()) == "" ||
			$.trim($('#contact').val()) == "" ||
			$.trim($('#fileupload').val()) == "") {
				alert("请填写完整");
				//return false;
			}
	} catch (e) {
		alert(e);
		return false;
	}
	return true;
}

function deleteEntry(id) {
	try {
		var confirmString = "删除数据.  您确认吗?\n" + $.trim($('#person').val()) + "\n" + $.trim($('#contact').val()) + "\n" + $.trim($('#description').val());
		if (window.confirm(confirmString)) {
			window.location="index.php?action=delete&id=" + id;
		}
	} catch (e) {
		alert(e);
		return false;
	}
	return true;

}