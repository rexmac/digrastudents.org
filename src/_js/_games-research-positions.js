$(function() {
  var $table = $('table.games-research');

  function formatData(data) {
    return data.replace(/^\?$/, '<em>unknown</em>');
  }

  function formatDetails(table, tr) {
    var aData = table.fnGetData(tr),
        details = aData[6],
        sOut = '<table><tbody>',
        link = details.link ? '<a href="' + details.link + '" target="_blank">' + details.link + '</a>' : formatData('?');

    sOut += '<tr><th>Research Group / Lab:</th><td>' + formatData(details.group) + '</td></tr>';
    sOut += '<tr><th>Focus/Specialization:</th><td>' + formatData(details.focus) + '</td></tr>';
    sOut += '<tr><th>Contact Person:</th><td>' + formatData(details.contact) + '</td></tr>';
    sOut += '<tr><th>Link:</th><td>' + link + '</td></tr>';
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
          '<i class="fa fa-plus-square-o"></i>',
          item.continent,
          item.country,
          formatData(item.university),
          formatData(item.department),
          formatData(item.program),
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
          {'bSortable': false, 'aTargets': [0]},
          {'sWidth': '6em', 'aTargets': [5]}
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
        e.preventDefault();
        var $tr = $(this).parent();
        if($table.fnIsOpen($tr[0])) {
          $tr.find('i').removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
          $table.fnClose($tr[0]);
        } else {
          $tr.find('i').removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
          $table.fnOpen($tr[0], formatDetails($table, $tr[0]), 'details');
        }
      });

      $('.games-research-timestamp').html('The content of the table was last updated ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research/positions'
  });
});
