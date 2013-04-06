<ul class="menu nav" data-behavior="BS.Dropdown">
     <li class="dropdown">
        <a data-trigger="BS.showPopup" data-bs-showpopup-url="<?= @route('option=people&view=session&layout=modal&return='.base64_encode(KRequest::url()))?>" href="<?= @route('option=people&view=session&layout=modal')?>" class="dropdown-toggle">
            Login                                               
        </a>
     </li>
 </ul>