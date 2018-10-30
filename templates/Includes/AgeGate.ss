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
<% require css("silverstripe-agegate/css/agegate.css") %>
<% require javascript("silverstripe-agegate/javascript/agegate.js") %>
<% end_if %>