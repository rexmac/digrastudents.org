$(function() {
  var $table = $('table.games-research');

  function formatData(data, format) {
    var truncated = data;

    if(format === 'twitterHandle') {
      return data.replace(/@/g, '');
    }

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
        notes = aData[5],
        sOut = '<table><tbody>';

    sOut += sprintf('<tr><th>%s:</th><td>%s</td></tr>',
      'Notes',
      notes
    );

    sOut += '</tbody></table>';

    return sOut;
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
          $headers = $('<tr/>'),
          d = new Date(data.date * 1000);

      $.each(data.data, function(i, item) {
        aaData.push([
          '<i class="icon-expand-alt"></i>',
          item.name,
          '<a href="http://twitter.com/' + item.twitter + '">' + item.twitter + '</a>',
          item.affiliations,
          linkify(item.website),
          item.notes
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
          $tr.find('i').removeClass('icon-collapse-alt').addClass('icon-expand-alt');
          $table.fnClose($tr[0]);
        } else {
          $tr.find('i').removeClass('icon-expand-alt').addClass('icon-collapse-alt');
          $table.fnOpen($tr[0], formatDetails($table, $tr[0]), 'details');
        }
      });

      $('.games-research-timestamp').html('The content of the table was last updated ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research/twitter'
  });
});
