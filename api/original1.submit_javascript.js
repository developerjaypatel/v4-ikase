<script>
var aSubmitFields = [];
for (var fieldNumber = 0; fieldNumber < numFields; fieldNumber ++) {
	var fieldName = getNthFieldName(fieldNumber);
	var val = getField(fieldName).value;
	
	//app.alert(fieldName + "=" + val, 3);
	aSubmitFields.push(val);
}

this.submitForm({
  cURL: "https://www.ikase.org/api/cfdf.php",
  aFields: aSubmitFields,
  cSubmitAs: "HTML" // the default, not needed here
});
</script>