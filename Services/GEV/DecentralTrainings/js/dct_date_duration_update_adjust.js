var old_time = {};
var old_date = {};

function ilInitDurationTime(prefix)
{

	if(typeof old_time[prefix] === 'undefined' ) {
		old_time[prefix] = {"h":null,"m":null};
	}

	old_time[prefix]["h"] = document.getElementById(prefix + "[start][time]_h").selectedIndex;
	old_time[prefix]["m"] = document.getElementById(prefix + "[start][time]_m").selectedIndex;

	document.getElementById(prefix + "[start][time]_h").setAttribute("onChange", "ilUpdateEndTime(\""+prefix+"\")");
	document.getElementById(prefix + "[start][time]_m").setAttribute("onChange", "ilUpdateEndTime(\""+prefix+"\")");
	
	$("#form_").submit(function() {
		document.getElementById(prefix + "[end][time]_h").disabled = false;
		document.getElementById(prefix + "[end][time]_m").disabled = false;
	});
}

function ilInitDurationDate(prefix)
{
	if(typeof old_date[prefix] === 'undefined' ) {
		old_date[prefix] = {"y":null,"m":null,"d":null};
	}

	old_date[prefix] = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[start][date]_m").selectedIndex, 
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1
		);

	document.getElementById(prefix + "[start][date]_y").setAttribute("onChange", "ilUpdateEndDateFields(\""+prefix+"\")");
	document.getElementById(prefix + "[start][date]_m").setAttribute("onChange", "ilUpdateEndDateFields(\""+prefix+"\")");
	document.getElementById(prefix + "[start][date]_d").setAttribute("onChange", "ilUpdateEndDateFields(\""+prefix+"\")");
	$("#form_").submit(function() {
		document.getElementById(prefix + "[end][date]_y").disabled = false;
		document.getElementById(prefix + "[end][date]_m").disabled = false;
		document.getElementById(prefix + "[end][date]_d").disabled = false;
	});
}


function ilUpdateEndTime(prefix)
{
	var start;	
	var end;
	var diff;

	start_h = document.getElementById(prefix + "[start][time]_h").selectedIndex;
	start_m = document.getElementById(prefix + "[start][time]_m").selectedIndex;
			

	end_h = document.getElementById(prefix + "[end][time]_h").selectedIndex;
	end_m = document.getElementById(prefix + "[end][time]_m").selectedIndex;
		
		
	diff_h = start_h - old_time[prefix]["h"];
	diff_m = start_m - old_time[prefix]["m"];

	//alert(end.toDateString());
	var end_hours_index = end_h + diff_h;
	var end_minute_index = end_m + diff_m;

	if(end_minute_index > 59) {
		end_minute_index = end_minute_index - 60;
		end_hours_index++;
	}

	if(end_hours_index > 23){
		var hour = document.getElementById(prefix + "[start][time]_h");
		hour.selectedIndex = old_time[prefix]["h"];
		var minute = document.getElementById(prefix + "[start][time]_m");
		minute.selectedIndex = old_time[prefix]["m"];

		alert('Die Trainings dürfen nicht in den nächsten Tag ragen.');
		return;
	}

	var hour = document.getElementById(prefix + "[end][time]_h");
	for(i = 0; i < hour.options.length;i++)
	{
		if(i == end_hours_index)
		{
			hour.selectedIndex = i;
			break;
		}
	}
	
	var minute = document.getElementById(prefix + "[end][time]_m");
	for(i = 0; i < minute.options.length;i++)
	{
		if(i == end_minute_index)
		{
			minute.selectedIndex = i;
			break;
		}
	}

	// Save current date
	old_time[prefix]["h"] = start_h;
	old_time[prefix]["m"] = start_m;
}

function ilUpdateEndDateFields(prefix)
{
	var start;	
	var end;
	var diff;

	start = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[start][date]_m").selectedIndex, 
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1
		);
			

	end = new Date(
			document.getElementById(prefix + "[end][date]_y").options[document.getElementById(prefix + "[end][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[end][date]_m").selectedIndex, 
			document.getElementById(prefix + "[end][date]_d").selectedIndex + 1
		);
		
	diff = end.getTime() - old_date[prefix].getTime();
	end.setTime(start.getTime() + diff);

	//alert(end.toDateString());
	var end_year = end.getFullYear();
	var end_month_index = end.getMonth();
	var end_day_index = end.getDate() - 1;

	var year = document.getElementById(prefix + "[end][date]_y");
	for(i = 0; i < year.options.length;i++)
	{
		if(end_year == year.options[i].value)
		{
			year.selectedIndex = i;
			break;
		}
	}
	
	var month = document.getElementById(prefix + "[end][date]_m");
	for(i = 0; i < month.options.length;i++)
	{
		if(i == end_month_index)
		{
			month.selectedIndex = i;
			break;
		}
	}

	var day = document.getElementById(prefix + "[end][date]_d");
	for(i = 0; i < day.options.length;i++)
	{
		if(i == end_day_index)
		{
			day.selectedIndex = i;
			break;
		}
	}

	// Save current date
	old_date[prefix] = start;
}