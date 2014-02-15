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

  function formatDetails(table, tr) {
    var aData = table.fnGetData(tr),
        details = aData[5],
        sOut = '<table><tbody>',
        link = details.link ? '<a href="' + details.link + '">' + details.link + '</a>' : '';

    sOut += sprintf('<tr><th>%s:</th><td>%s</td><td class="spacer">&nbsp;</td><th>%s:</th><td>%s (%s)</td></tr>',
      'Submission Guidelines',
      formatData(details.submissionGuidelinesUrl, 'url'),
      'Word Limit (brief limit)',
      formatData(details.wordLimit),
      formatData(details.briefWordLimit)
    );
    sOut += sprintf('<tr><th>%s:</th><td>%s</td><td class="spacer">&nbsp;</td><th>%s:</th><td>%s</td></tr>',
      'Journal Reviewer Profile',
      formatData(details.journalReviewerUrl, 'url'),
      'Impact factor',
      formatData(details.impactFactor)
    );
    sOut += sprintf('<tr><th>%s:</th><td>%s</td><td class="spacer">&nbsp;</td><th>%s:</th><td>%s</td></tr>',
      'ISSN',
      formatData(details.issn),
      'h5-index',
      formatData(details.h5Index)
    );
    sOut += sprintf('<tr><th>%s:</th><td>%s</td><td class="spacer">&nbsp;</td><th>%s:</th><td>%s</td></tr>',
      'eISSN',
      formatData(details.eissn),
      'h5-median',
      formatData(details.h5Median)
    );

    sOut += '</tbody></table>';

    return sOut;
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
          '<i class="fa fa-expand-alt"></i>',
          '<a href="' + item.homepage + '">' + item.journal + '</a>',
          item.discipline,
          '<a href="' + item.publisherHomepage + '">' + item.publisher + '</a>',
          formatData(item.frequency),
          item.details
        ]);
      });

      $headers.append('<th></th>');
      $.each(data.headers, function(i, item) {
        $headers.append('<th>' + item + '</th>')
      });

      $table.children('thead').empty().append($headers);
      $table.children('tbody').empty();
      $table.dataTable({
        'aaData': aaData,
        'aaSorting': [[1, 'asc']],
        'aoColumnDefs': [
          {'bSortable': false, 'aTargets': [0]}/*,
          {'sWidth': '6em', 'aTargets': [5]}*/
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

      $table.$('td').not('.details').click(function(e) {
        var $tr = $(this).parent();
        e.preventDefault();

        if($(e.target).is('a')) {
          window.open($(e.target).attr('href'), '_blank');
          return;
        }

        if($table.fnIsOpen($tr[0])) {
          $tr.find('i').removeClass('fa-collapse-alt').addClass('fa-expand-alt');
          $table.fnClose($tr[0]);
        } else {
          $tr.find('i').removeClass('fa-expand-alt').addClass('fa-collapse-alt');
          $table.fnOpen($tr[0], formatDetails($table, $tr[0]), 'details');
        }
      });

      $('.games-research-timestamp').html('The content of the table was last updated ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research/journals'
  });
});
