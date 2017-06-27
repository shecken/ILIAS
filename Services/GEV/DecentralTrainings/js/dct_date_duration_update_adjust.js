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
	var o_start_h;
	var o_start_m;
	var o_end_h;
	var o_end_m;
	var diff;

	var start_h;
	var start_m;
	var end_h;
	var end_m;
	var start_h_i;
	var start_m_i;



	o_start_h = document.getElementById(prefix + "[start][time]_h");
	o_start_m = document.getElementById(prefix + "[start][time]_m");
	o_end_h =  document.getElementById(prefix + "[end][time]_h");
	o_end_m =  document.getElementById(prefix + "[end][time]_m")


	start_h_i = o_start_h.selectedIndex;
	start_m_i = o_start_m.selectedIndex;

	start_h = Number(o_start_h.options[start_h_i].value);
	start_m = Number(o_start_m.options[start_m_i].value);
			

	end_h = Number(o_end_h.options[o_end_h.selectedIndex].value);
	end_m = Number(o_end_m.options[o_end_m.selectedIndex].value);

		
	diff_h = start_h - Number(o_start_h.options[old_time[prefix]["h"]].value);
	diff_m = start_m - Number(o_start_m.options[old_time[prefix]["m"]].value);

	//alert(end.toDateString());
	var end_h_new = end_h + diff_h;
	var end_m_new = end_m + diff_m;

	if(end_m_new > 59) {
		end_m_new = end_m_new - 60;
		end_h_new = end_h_new + 1;
	}

	if(end_m_new < 0) {
		end_m_new = end_m_new + 60;
		end_h_new = end_h_new - 1;
	}


	if(end_h_new > 23){
		o_start_h.selectedIndex = old_time[prefix]["h"];
		o_start_m.selectedIndex = old_time[prefix]["m"];

		alert('Die Trainings dürfen nicht in den nächsten Tag ragen.');
		return;
	}

	for(i = 0; i < o_end_h.options.length;i++)
	{
		if(Number(o_end_h.options[i].value) == end_h_new)
		{
			o_end_h.selectedIndex = i;
			break;
		}
	}
	
	for(i = 0; i < o_end_m.options.length;i++)
	{
		if(Number(o_end_m.options[i].value) == end_m_new)
		{
			o_end_m.selectedIndex = i;
			break;
		}
	}

	// Save current date
	old_time[prefix]["h"] = start_h_i;
	old_time[prefix]["m"] = start_m_i;
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