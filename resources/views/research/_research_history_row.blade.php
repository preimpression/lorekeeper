<tr class="outflow">
    <td>{!! $log->research ? $log->research->displayName : '(Deleted Research)' !!}</td>
    <td>{!! $log->tree ? $log->tree->displayName : '(Deleted Tree)' !!}</td>
    <td>{!! json_decode($log->data,true)['log_type'] !!}</td>
    <td>{!! json_decode($log->data,true)['message'] !!}</td>
    <td>{!! $log->currency ? $log->currency->display($log->cost) : $log->cost . ' (Deleted Currency)' !!}</td>
    <td class="text-right">{!! format_date($log->created_at,true) !!}</td>
</tr>