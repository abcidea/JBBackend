
function nTab2(thisObj,Num){

if(thisObj.className == "current")return;
var tabObj = thisObj.parentNode.id;

var tabList = document.getElementById(tabObj).getElementsByTagName("li");
for(i=0; i <tabList.length; i++)
{
  if (i == Num)
  {
   thisObj.className = "current"; 
      document.getElementById(tabObj+"_sub"+i).style.display = "block";
  }else{
   tabList[i].className = "link"; 
   document.getElementById(tabObj+"_sub"+i).style.display = "none";
  }
} 
}