<article class="update-version">
    <header>
        <h3>{{__ 'TAO'}} {{version}}</h3>
    </header>
    <div>
    {{#each messages}}
         <div class="feedback feedback-{{@key}} small">
            <span class="icon-{{@key}}"></span>
            <ul class="circle">
            {{#each this}}
                <li>{{this}}</li>
            {{/each}}
            </ul>
         </div>
    {{/each}}
    </div>
    <div>
        <button class="btn-success upgrader" data-version="{{version}}" data-file="{{file}}"><span class="icon-play"></span>{{__ 'Update to'}} {{version}}</button>
    </div>
    <div class="upgrading hidden">
        <h4>{{__ 'Upgrade in progress...'}}</h4>
        <span class="message"></span>
        <div class="status"></div>
    </div>
</article>

