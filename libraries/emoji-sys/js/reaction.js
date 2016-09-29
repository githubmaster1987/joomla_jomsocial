$(document).ready(function(){
  $(".reaction").on("click",function(){   // like click
	var data_reaction = $(this).attr("data-reaction");
	$(".like-btn-emo").removeClass().addClass('like-btn-emo').addClass(data_reaction.toLowerCase() + '-btn-default');
	$(".like-btn-text").text(data_reaction).removeClass().addClass('like-btn-text').addClass('like-btn-text-'+data_reaction.toLowerCase()).addClass("active");;

	if(data_reaction == "Love"){
	  $(".love-emo").html('<span class="like-btn-emo love-btn-default"></span>');
	  $(".love-details").html("7000");
	}
	else if(data_reaction == "Like"){
	  $(".like-emo").html('<span class="like-btn-default"></span>');	
	  $(".like-details").html("6000");
	}
	else if(data_reaction == "HaHa"){
	  $(".haha-emo").html('<span class="haha-btn-default"></span>');	
	  $(".haha-details").html("5000");
	}
	else if(data_reaction == "Wow"){
	  $(".wow-emo").html('<span class="wow-btn-default"></span>');	
	  $(".wow-details").html("2000");
	}
	else if(data_reaction == "Sad"){
	  $(".sad-emo").html('<span class="sad-btn-default"></span>');	
	  $(".sad-details").html("3000");
	}
	else if(data_reaction == "Angry"){
	  $(".angry-emo").html('<span class="angry-btn-default"></span>');		
	  $(".angry-details").html("4000");
	}

	insert_cnt(data_reaction);
  });

  var load_cnt = function()
	{

		$.ajax({
			type: "Get",
			url: "index.php?option=com_community&view=frontpage&task=ajax&type=select",
			    data: {
			        act_type: "status",
			        act_id: 1,
			    },
			cache: false,
			success: function(html)
			{
			  
				//console.log(html[]);
			  $(".love-details").html("5000");
			  $(".like-details").html("6000");
			  $(".haha-details").html("5000");
			  $(".wow-details").html("2000");
			  $(".sad-details").html("3000");
			  $(".angry-details").html("4000");
			} 
		});
	}

	var insert_cnt = function(emo_type)
	{
		$.ajax({
			type: "Get",
			url: "index.php?option=com_community&view=frontpage&task=ajax&type=update",
			    data: {
			        act_type: "status",
			        act_id: 1,
			    },
			cache: false,
			success: function(html)
			{
				console.log(html);
			} 
		});
	 }
	
    load_cnt();
});