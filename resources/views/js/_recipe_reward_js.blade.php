
<script>
$( document ).ready(function() {    
    var $rewardTable  = $('#rewardTableBody');
    var $rewardRow = $('#rewardRow').find('.reward-row');
    var $itemSelect = $('#rewardRowData').find('.item-select');
    var $currencySelect = $('#rewardRowData').find('.currency-select');

    $('#rewardTableBody .selectize').selectize();
    attachRemoveListener($('#rewardTableBody .remove-reward-button'));

    $('#addReward').on('click', function(e) {
        e.preventDefault();
        var $clone = $rewardRow.clone();
        $rewardTable.append($clone);
        attachRewardTypeListener($clone.find('.reward-type'));
        attachRemoveListener($clone.find('.remove-reward-button'));
    });

    $('.reward-type').on('change', function(e) {
        var val = $(this).val();
        var $cell = $(this).parent().find('.reward-row-select');

        var $clone = null;
        if(val == 'Item') $clone = $itemSelect.clone();
        else if (val == 'Currency') $clone = $currencySelect.clone();
        else if (val == 'RewardTable') $clone = $tableSelect.clone();

        $cell.html('');
        $cell.append($clone);
    });

    function attachRewardTypeListener(node) {
        node.on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.reward-row-select');

            var $clone = null;
            if(val == 'Item') $clone = $itemSelect.clone();
            else if (val == 'Currency') $clone = $currencySelect.clone();
            else if (val == 'RewardTable') $clone = $tableSelect.clone();

            $cell.html('');
            $cell.append($clone);
            $clone.selectize();
        });
    }

    function attachRemoveListener(node) {
        node.on('click', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }

});
    
</script>