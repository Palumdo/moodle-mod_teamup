// This may be set to true later in the page.
var interaction_disabled    = false;
var str                     = [];
var typesDisplay            = {};
var msgDisplay            = {};

$(function() {
    $.ajax({
      dataType: "json",
      async: false,
      url: "strings.php",
      success: function(data){
          str = data.trans;
          buildstrings();
      }
    });

    for (var i in init_questions)    {
      
        $("#question-" + i).data("question", init_questions[i]);
        $("#question-" + i + " div.qobject").html(JSON.stringify(init_questions[i]));
        
        var x = $("#question-" + i + " .type");
        x.html(typesDisplay[x.html()]);
    }

    if(interaction_disabled == false)    {
        $("#questions").sortable({handle : '.handle', axis : 'y'});
        $("#questions .answers ul").sortable({axis : 'y'}).find("li").css("cursor","default");

        $("#answerSection input").on('keydown', function(evt) {
            if((evt.which == 13) && !(evt.metaKey || evt.ctrlKey))
            {
                if ($(this).nextAll("input:first").length == 0) {
                    addNewAnswer();
                } else {
                    $(this).nextAll("input:first").focus();
                }
            } else if(evt.which == 38) {
                $(this).prevAll("input:first").focus();
            } else if(evt.which == 40) {
                $(this).nextAll("input:first").focus();
            }
        });

        $("#newQuestionForm input").on('keydown', function(evt) {
            if((evt.which == 13) && (evt.metaKey || evt.ctrlKey))
            {
                $("#addNewQuestion").click();
                $("#newQuestionForm input[name='question']").focus();
            }
        });
    } else   {
        $("td.edit a").remove();
    }

    $.fn.wait = function(time, type) {
        time = time || 1000;
        type = type || "fx";
        return this.queue(type, function() {
            var self = this;
            setTimeout(function() {
                $(self).dequeue();
            }, time);
        });
    };

    $("#importButton").click(function() {
        window.location.href = window.location.href + "&import=" + $(this).prevAll("select:first").val();
    });
    $(".answerText").dblclick(function() {
      $(this).after().html('<input class="text" id="newvalue" style="width:200px;" value="'+$(this).html()+'"><button id="savethisAnswer" onclick="saveAnswer(this,'+$(this).data("id")+');" class="btn btn-primary">X</button>');
    });   
});

function saveTitle(obj, val) {
  $('[data-id='+val+']').html($(obj).prev().val());
  $(obj).prev().remove();
  $(obj).remove();
}  

function saveAnswer(obj, val) {
  $('[data-id='+val+']').html($(obj).prev().val());
  $(obj).prev().remove();
  $(obj).remove();
}  


function addNewAnswer() {
    $('<input type="text" name="answers[]" class="text" /><br/>').insertBefore("#answerSection button:first").focus();
    return false;
}

function removeLastAnswer() {
    $('#answerSection input:last').next().remove();
    $('#answerSection input:last').remove();
}

function addNewQuestion() {
    var question = {};
    question['question'] = $("#newQuestionForm input[name='question']").val();
    question['type'] = $("#newQuestionForm select").val();
    question['answers'] = [];
    $("#answerSection input[type='text']").each(function()    {
        if ($.trim($(this).val()).length) {
            question['answers'].push($(this).val());
        }
    })

    // Validate.
    err = [];
    if ($.trim(question.question) == '') {
        err.push(msgDisplay.onequest);
    }
    if (question.answers.length <= 1) {
      err.push(msgDisplay.two);
    } else if (question['type'] == 'three' && question.answers.length <= 3) {
      err.push(msgDisplay.three);
    } else if (question['type'] == 'four' && question.answers.length <= 4) {
      err.push(msgDisplay.four);
    } else if (question['type'] == 'five' && question.answers.length <= 5) {
      err.push(msgDisplay.five);
    }  
        
    if (err.length)    {
        alert(err.join("\n"));
        return;
    }

    // Initialise the view.
    questionView = $(views.question);
    questionView.find(".questionText").html(question['question']);
    questionView.find(".type").html(typesDisplay[question['type']]);
    questionView.find(".answers").html("<ul><li>" + question['answers'].join("</li><li>") + "</li></ul>");
    questionView.find(".qobject").html(JSON.stringify(question));
    
    $("#questions").append(questionView);
    // Reset the form.
    $("#newQuestionForm input").val("");

    $("#saveQuestionnaire").click();

    return false;
}

function deleteQuestion(object) {
    $(object).closest("div.question").slideUp(300,function(){
        $(this).remove();
    });
}

function saveQuestionnaire(url, id) {
    $('#savethisAnswer').click();
    
    var questions = $("#questions div.question");
    var questiondata = [];
    if (questions.length < 1) {
        alert(msgDisplay.pleaseatleastonequestion);
        return;
    }

    // Iterate over the UI to get question and answer order.
    questions.each(function() {
        // Reorder answers.
        var question = JSON.parse($(this).find("div.qobject").html());
        question.answers = [];
        $(this).find(".answers ul li").each(function(){
            question.answers.push($(this).html());
        })
        questiondata.push(question);
    });

    $("#savingIndicator").html(msgDisplay.saving).slideDown(300);

    $.post(url,{'id' : id, 'action' : 'saveQuestionnaire', 'input' : JSON.stringify(questiondata)},function(data) {
        for(i in data.questionnaire) {
            o = data.questionnaire[i];
            questions.eq(parseInt(o.ordinal)).data("question",o);
        }
        $("#savingIndicator").html(msgDisplay.saved).slideUp(300); //DPL .wait(2000).slideUp(300);
    },'json')
}

var views = {
    'question' : '<div class="question"><table> \
	<tr> \
		<td rowspan="2" class="handle">&nbsp;</td> \
		<td><span class="questionText"></span> <span class="type"></span></td> \
		<td colspan="2" class="edit"> \
			<a onclick="deleteQuestion(this)">Supprimer</a> \
      <div class="qobject" style="display:none;"></div> \
		</td> \
	</tr> \
	<tr> \
		<td class="answers" colspan="2"></td> \
	</tr></table></div>'
};

function buildstrings() {
    typesDisplay.one        = str[0];
    typesDisplay.any        = str[1];
    typesDisplay.atleastone = str[2];
    typesDisplay.two        = str[3];
    typesDisplay.three      = str[4];
    typesDisplay.four       = str[5];
    typesDisplay.five       = str[6];
    msgDisplay.two          = str[7];    
    msgDisplay.three        = str[8];    
    msgDisplay.four         = str[9];    
    msgDisplay.five         = str[10];    
    msgDisplay.onequest     = str[11];
    msgDisplay.pleaseatleastonequestion = str[12];
    msgDisplay.saving       = str[13];
    msgDisplay.saved        = str[14];    
}
