$(function() {
  var $table = $('table.games-research');

  function formatData(data, format) {
    var truncated = data;

    if(format === 'url') {
      if(data && !/^\?$/.test(data)) {
        if(data.length > 42) {
          truncated = data.substr(0, 29) + '&hellip;' + data.substr(data.length - 12);
        }
        return '<a href="' + data + '" target="_blank">' + truncated + '</a>';
      }
    }

    return data.replace(/^\?$/, '<em>unknown</em>');
  }

  $.ajax({
    beforeSend: function() {
      $table.hide().after('<div class="align-content-center"><span class="games-research loading"><i class="fa fa-spinner fa-spin fa-2x"></i><span>Loading content...</span></span></div>');
    },
    complete: function() {
      $('.games-research.loading').parent().remove();
    },
    success: function(data, textStatus, jqXhr) {
      var aaData = [],
          $headers = $('<tr/>'),
          d = new Date(data.date * 1000);

      $.each(data.data, function(i, item) {
        aaData.push([
          item.category,
          item['sub-category'],
          item.type,
          item.year,
          item.authors,
          '<a href="' + item.link + '">' + item.title + '</a>',
          item.publisher,
          '<div class="altmetric-embed-placeholder" data-badge-type="1" data-badge-popover="bottom" data-doi="' + item.doi + '" data-pmid="' + item.pubmed + '"></div>'
        ]);
      });

      $.each(data.headers, function(i, item) {
        $headers.append('<th>' + item + '</th>');
      });
      $headers.append('<th>Altmetrics</th>');

      $table.children('thead').empty().append($headers);
      $table.children('tbody').empty();
      $table.dataTable({
        'aaData': aaData,
        'aaSorting': [],
        'aoColumnDefs': [
          {'bSortable': false, 'aTargets': [7]},
          {'bSearchable': false, 'aTargets': [7]}
        ],
        'iDisplayLength': 25,
        'oLanguage': {
          'sLengthMenu': 'Show _MENU_ entries per page',
          'sSearch': '_INPUT_',
          'sZeroRecords': 'No data found'
        },
        'sDom': 'frtipl',
        'bLengthChange': false,
        'bPaginate': false
      }).rowGrouping({
        'asExpandedGroups': 'NONE',
        'iGroupingColumnIndex2': 1,
        'fnGroupLabelFormat': function(label) { return '' + label + ' (category)'; },
        'fnGroupLabelFormat2': function(label) { return '' + label + ' (sub-category)'; },
        'bExpandableGrouping': true,
        'bExpandableGrouping2': true,
        'fnOnInit': function() {
          $table.find('.subgroup').trigger('click');
        },
        'fnOnGroupExpanded': function(a, b) {
          var init = false;
          if(a.level === 1) { // this is a sub-group expansion
            $('tr[data-group="' + a.dataGroup + '"]').find('.altmetric-embed-placeholder').each(function() {
              if($(this).data('doi') || $(this).data('pmid')) {
                init = true;
                $(this).removeClass('altmetric-embed-placeholder').addClass('altmetric-embed');
              }
            });
            if(init) {
              _altmetric_embed_init();
            }
          }
        }
      }).show();

      $('#DataTables_Table_0_filter input').fontAwesomeSearchPolyfill();
      $('#DataTables_Table_0_filter input').attr('placeholder', 'Search this table...');

      $('.games-research-timestamp').html('The content of the table was last updated ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research/articles'
  });
});
