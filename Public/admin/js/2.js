window.onload = function(){
	var oLeft = document.getElementById("left");
	var oUserManage = document.getElementById("user_manage");
	var oTestManage = document.getElementById("test_manage");
	var oUserUl = document.getElementById("user_ul");
	var oTestUl = document.getElementById("test_ul");
	oUserManage.onclick = function(){
		if(oUserUl.style.display == 'block'){
				oUserUl.style.display = 'none';
				
			}else{
				oUserUl.style.display = 'block';
				oTestUl.style.display = 'none';
				
			}
	};
	oTestManage.onclick = function(){
		if(oTestUl.style.display == 'block'){
				oTestUl.style.display = 'none';
				
			}else{
				oTestUl.style.display = 'block';
				oUserUl.style.display = 'none';
				
			}
	};
}