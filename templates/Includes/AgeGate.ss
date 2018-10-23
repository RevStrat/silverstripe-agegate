<% if ShowAgeGate %>
<div class="AgeGate">
    <div class="overlay">
        <div class="content">
            <% if AgeGateContentOverride %>
                $AgeGateContentOverride
            <% else %>
                $SiteConfig.AgeGateContent
            <% end_if %>
            $AgeGateForm
        </div>
    </div>
</div>
<% require css("RevStrat/silverstripe-agegate:css/agegate.css") %>
<% require javascript("RevStrat/silverstripe-agegate:javascript/agegate.js") %>
<% end_if %>