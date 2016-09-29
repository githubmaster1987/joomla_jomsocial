$(document).ready(function(){
  $(".reaction").on("click",function(){   // like click
	var data_reaction = $(this).attr("data-reaction");
	$(".like-btn-emo").removeClass().addClass('like-btn-emo').addClass(data_reaction.toLowerCase() + '-btn-default');
	$(".like-btn-text").text(data_reaction).removeClass().addClass('like-btn-text').addClass('like-btn-text-'+data_reaction.toLowerCase()).addClass("active");;

	insert_cnt(data_reaction);
  });

  var load_cnt = function()
	{

		$.ajax({
			type: "POST",
			url: "index.php?option=com_community&view=frontpage&task=ajax&type=select",
			    data: {
			        act_type: "stream",
			        act_id: 15
			    },
			cache: false,
			success: function(html)
			{
				var obj = JSON.parse(html);
				if(obj.length > 0)
				{
					$(".love-details").html(obj[0].love_cnt);
					$(".like-details").html(obj[0].like_cnt);
					$(".haha-details").html(obj[0].haha_cnt);
					$(".wow-details").html(obj[0].wow_cnt);
					$(".sad-details").html(obj[0].sad_cnt);
					$(".angry-details").html(obj[0].angry_cnt);		
				}
			  
			} 
		});
	}

	var insert_cnt = function(emo_type)
	{
		$.ajax({
			type: "POST",
			url: "index.php?option=com_community&view=frontpage&task=ajax&type=update",
			    data: {
			        act_type: "stream",
			        act_id: 15,
			        emo_type: emo_type
			    },
			cache: false,
			success: function(html)
			{
				load_cnt();
			} 
		});
	 }
	
    load_cnt();
});