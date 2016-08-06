window.onload = function(){
	var oAchievement = document.getElementById("achievement");
	var atr = oAchievement.getElementsByTagName("tr");
	var oScore = document.getElementById("score");
	var aImg = oScore.getElementsByTagName('img');
	var oExplain = document.getElementById("explain");
	for(var i = 0;i<atr.length;i++){
		atr[i].onclick = function(){
			if(oScore.style.display == 'block'){
				oScore.style.display = 'none';
				oExplain.style.display = 'none';
			}else{
				oScore.style.display = 'block';
			}
			
		};
	}
	for(var j = 0;j<aImg.length;j++){
		aImg[j].onclick = function(){
			if(oExplain.style.display == 'block'){
				oExplain.style.display = 'none';
			}else{
				oExplain.style.display = 'block';
			}
		}
	}

};
