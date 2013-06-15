$(function() {
  var $table = $('table.games-research-map');

      function formatDetails(table, tr) {
        var aData = table.fnGetData(tr),
            details = aData[6],
            sOut = '<table><tbody>',
            link = details.link ? '<a href="' + details.link + '">' + details.link + '</a>' : '';

        sOut += '<tr><th>Research Group / Lab:</th><td>' + details.group + '</td></tr>';
        sOut += '<tr><th>Focus/Specialization:</th><td>' + details.focus + '</td></tr>';
        sOut += '<tr><th>Contact Person:</th><td>' + details.contact + '</td></tr>';
        sOut += '<tr><th>Link:</th><td>' + link + '</td></tr>';
        sOut += '</tbody></table>';

        return sOut;
      }

  $.ajax({
    beforeSend: function() {
      $table.hide().after('<div class="align-content-center"><span class="games-research-map loading"><i class="icon-spinner icon-spin icon-2x"></i><span>Loading content...</span></span></div>');
    },
    complete: function() {
      $('.games-research-map.loading').parent().remove();
    },
    success: function(data, textStatus, jqXhr) {
      var aaData = [],
          $headers = $('<tr/>'),
          d = new Date(data.date * 1000);

      $.each(data.data, function(i, item) {
        //aaData.push([item.continent, item.country, item.university, item.department, item.group, item.program, item.contact, item.link, item.focus]);
        aaData.push(['<i class="icon-expand-alt"></i>', item.continent, item.country, item.university, item.department, item.program, item.details]);
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

      $table.$('td').not('.details').click(function(e) {
        e.preventDefault();
        var $tr = $(this).parent();
        if($table.fnIsOpen($tr[0])) {
          $tr.find('i').removeClass('icon-collapse-alt').addClass('icon-expand-alt');
          $table.fnClose($tr[0]);
        } else {
          $tr.find('i').removeClass('icon-expand-alt').addClass('icon-collapse-alt');
          $table.fnOpen($tr[0], formatDetails($table, $tr[0]), 'details');
        }
      });

      $('.games-research-map-timestamp').html('The content of the table is current as of ' + d.toUTCString() + '.');
    },
    type: 'GET',
    url: '/games-research-map/data'
  });
});
