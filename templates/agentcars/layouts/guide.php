<?php

use yii\apidoc\templates\agentcars\SideNavWidget;

/* @var $this yii\web\View */
/* @var $content string */
/* @var $chapters array */

if (isset($currentFile)) {
    foreach ($chapters as $chapter) {
        foreach ($chapter['content'] as $chContent) {
            if ($chContent['file'] == basename($currentFile)) {
                $guideHeadline = "{$chContent['headline']} - {$chapter['headline']}";
            }
        }
    }
}

$this->beginContent('@yii/apidoc/templates/agentcars/layouts/main.php', isset($guideHeadline) ? ['guideHeadline' => $guideHeadline] : []); ?>

<div class="row">
    <div class="col-md-2">
        <?php
        $nav = [];
        if ($this->context->guideUrl !== null) {
            $nav[] = ['label' => 'Guide', 'url' => rtrim($this->context->guideUrl, '/') . '/' . $this->context->guidePrefix . 'README.html'];
        }
        foreach ($chapters as $chapter) {
            $items = [];
            foreach($chapter['content'] as $chContent) {
                $items[] = [
                    'label' => $chContent['headline'],
                    'url' => $this->context->generateGuideUrl($chContent['file']),
                    'active' => isset($currentFile) && ($chContent['file'] == basename($currentFile)),
                ];
            }
            $nav[] = [
                'label' => $chapter['headline'],
//                'url' => $this->context->generateGuideUrl($file),
                'items' => $items,
            ];
        } ?>
        <?= SideNavWidget::widget([
            'id' => 'navigation',
            'items' => $nav,
            'view' => $this,
        ]) ?>
        <div class="navbar-form navbar-left" role="search">
            <div class="form-group">
                <input id="searchbox" type="text" class="form-control" placeholder="Search">
            </div>
        </div>
    </div>
    <div class="col-md-9 guide-content" role="main">
        <?= $content ?>
        <div class="toplink"><a href="#" class="h1" title="go to top"><span class="glyphicon glyphicon-arrow-up"></span></a></div>
    </div>
</div>
<?php
\yii\apidoc\templates\agentcars\assets\JsSearchAsset::register($this);

// defer loading of the search index: https://developers.google.com/speed/docs/best-practices/payload?csw=1#DeferLoadingJS
$this->registerJs(<<<JS
var element = document.createElement("script");
element.src = "./jssearch.index.js";
document.body.appendChild(element);
JS
);

$this->registerJs(<<<JS

var searchBox = $('#searchbox');

// search when typing in search field
searchBox.on("keyup", function(event) {
    var query = $(this).val();

    if (query == '' || event.which == 27) {
        $('#search-resultbox').hide();
        return;
    } else if (event.which == 13) {
        var selectedLink = $('#search-resultbox a.selected');
        if (selectedLink.length != 0) {
            document.location = selectedLink.attr('href');
            return;
        }
    } else if (event.which == 38 || event.which == 40) {
        $('#search-resultbox').show();

        var selected = $('#search-resultbox a.selected');
        if (selected.length == 0) {
            $('#search-results').find('a').first().addClass('selected');
        } else {
            var next;
            if (event.which == 40) {
                next = selected.parent().next().find('a').first();
            } else {
                next = selected.parent().prev().find('a').first();
            }
            if (next.length != 0) {
                var resultbox = $('#search-results');
                var position = next.position();

//              TODO scrolling is buggy and jumps around
//                resultbox.scrollTop(Math.floor(position.top));
//                console.log(position.top);

                selected.removeClass('selected');
                next.addClass('selected');
            }
        }

        return;
    }
    $('#search-resultbox').show();
    $('#search-results').html('<li><span class="no-results">No results</span></li>');

    var result = jssearch.search(query);

    if (result.length > 0) {
        var i = 0;
        var resHtml = '';

        for (var key in result) {
            if (i++ > 20) {
                break;
            }
            resHtml = resHtml +
            '<li><a href="' + result[key].file.u.substr(3) +'"><span class="title">' + result[key].file.t + '</span>' +
            '<span class="description">' + result[key].file.d + '</span></a></li>';
        }
        $('#search-results').html(resHtml);
    }
});

// hide the search results on ESC
$(document).on("keyup", function(event) { if (event.which == 27) { $('#search-resultbox').hide(); } });
// hide search results on click to document
$(document).bind('click', function (e) { $('#search-resultbox').hide(); });
// except the following:
searchBox.bind('click', function(e) { e.stopPropagation(); });
$('#search-resultbox').bind('click', function(e) { e.stopPropagation(); });

JS
); ?>

<div id="search-resultbox" style="display: none;" class="modal-content">
    <ul id="search-results">
    </ul>
</div>
<?php $this->endContent(); ?>
