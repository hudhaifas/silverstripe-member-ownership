<div class="timeline">
    <!-- Line component -->
    <div class="line text-muted"></div>

    <% loop LastEditedObjects.GroupedBy(DayLastEdited) %>
    <article class="panel panel-danger panel-outline panel-date">
        <div class="panel-heading icon">
            <i class="fa fa-calendar-o"></i>
        </div>

        <div class="panel-body">
            <strong>$DayLastEdited</strong>
        </div>
    </article>

        <% loop $Children %>
        <article class="panel panel-danger panel-outline panel-activity">
            <div class="panel-heading icon">
                <i class="fa fa-pencil"></i>
            </div>

            <div class="panel-body">
                <strong><a href="$ObjectLink">$ObjectTitle</a></strong>.
            </div>
        </article>
        <% end_loop %>
    <% end_loop %>
</div>
