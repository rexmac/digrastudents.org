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
      $table.hide().after('<div class="align-content-center"><span class="games-research loading"><i class="icon-spinner icon-spin icon-2x"></i><span>Loading content...</span></span></div>');
    },
    complete: function() {
      $('.games-research.loading').parent().remove();
    },
    success: function(data, textStatus, jqXhr) {
      var aaData = [],
          $headers = $('<tr/>');

      $.each(data.data, function(i, item) {
        aaData.push([
          item.category,
          item['sub-category'],
          item.type,
          item.year,
          item.authors,
          '<a href="' + item.link + '">' + item.title + '</a>',
          item.publisher
        ]);
      });

      $.each(data.headers, function(i, item) {
        $headers.append('<th>' + item + '</th>')
      });

      $table.children('thead').empty().append($headers);
      $table.children('tbody').empty();
      $table.dataTable({
        'aaData': aaData,
        'aaSorting': [],
        'aoColumnDefs': [
        ],
        'iDisplayLength': 25,
        'oLanguage': {
          'sLengthMenu': 'Show _MENU_ entries per page',
          'sSearch': '_INPUT_',
          'sZeroRecords': 'No data found'
        },
        'sDom': 'frtipl'
      }).show();

      $('#DataTables_Table_0_filter input').fontAwesomeSearchPolyfill();
      $('#DataTables_Table_0_filter input').attr('placeholder', 'Search this table...');

      $('.games-research-timestamp').html('The content of the table was last updated ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research/articles'
  });
});
