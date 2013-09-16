var openTab = "";

window.onload = function() {
	OpenTab("info");
};
function ToggleDropDown() {
	var s = document.getElementById("dropdown").style;
	s.display = s.display == "block" ? "none" : "block";
}
function OpenTab(tabName) {
	if (openTab != tabName) {
		var tpHTML = document.getElementById("tp-"+tabName).innerHTML;
		document.getElementById("tabContent").innerHTML = tpHTML;

		var tabItems = document.getElementById("tabList").getElementsByTagName("li");
		var selectedTabItem = document.getElementById("tabItem-"+tabName);

		for (var i=0; i<tabItems.length; i++) {
			tabItems[i].className = tabItems[i] == selectedTabItem ? "selected" : "not-selected";
		}

		openTab = tabName;
	}
}