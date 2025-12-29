<?php
require_once './includes/header.php';
$siteData = require("./siteControllers/fetch_sitedata.php");
$packages = $siteData['packages'] ?? [];

    if(isset($_GET["coupon"])){
        $recievedCoupon=$_GET["coupon"];
        $name=$_GET["name"] ?? '';
        $phone=$_GET["phone"] ?? '';
    }

?>

<section class="py-5 mt-20" style="background: var(--section-even-bg);">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold heading-text">Our Packages</h2>
      <p class="p-text">
        At <strong>UMID Infotech</strong>, we provide tailored digital solutions to grow your business – 
        from simple informative websites to custom applications and software solutions.
      </p>
    </div>

    <div class="row g-4">
      <?php if (!empty($packages)): ?>
        <?php foreach ($packages as $pkg):
          $title     = htmlspecialchars($pkg['title']);
          $desc      = nl2br(htmlspecialchars($pkg['description'] ?? ''));
          $price     = (float)$pkg['price'];
          $features  = $pkg['features'] ?? []; // each feature = ['feature','feature_price','status']

          // Filter for display in card: only checked
          $checkedFeatures = array_filter($features, fn($f) => $f['status'] === 'checked');

          // JSON encode all features for modal
          $featuresJson = htmlspecialchars(json_encode($features, JSON_UNESCAPED_UNICODE));
        ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card package-card shadow-sm h-100 text-center p-4 d-flex flex-column">
              <h4 class="fw-bold mb-3 card-title-text"><?php echo $title; ?></h4>
              <p class="p-text"><?php echo $desc; ?></p>
              <hr>
              <?php if (!empty($checkedFeatures)): ?>
                <ul class="list-unstyled text-start small flex-grow-1">
                  <?php foreach ($checkedFeatures as $feat): ?>
                    <li class="p-text">✔ <?php echo htmlspecialchars($feat['feature']); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p class="text-muted small flex-grow-1">No features listed</p>
              <?php endif; ?>
              <div class="mt-auto">
                <div class="price-tag fw-bold">
                  Starting at ₹<?php echo number_format($price, 2); ?>
                </div>
                <button
                  class="btn btn-outline-primary mt-3"
                  data-bs-toggle="modal"
                  data-bs-target="#getStartedModal"
                  data-title="<?php echo $title; ?>"
                  data-price="<?php echo htmlspecialchars(number_format($price, 2, '.', ''), ENT_QUOTES); ?>"
                  data-features='<?php echo $featuresJson; ?>'
                >
                  Get Started
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-muted">No packages available at the moment.</p>
      <?php endif; ?>
    </div>
  </div>
</section>


<!-- Get Started Modal -->
<div class="modal fade" id="getStartedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content modal-custom" style="max-height: 80vh; overflow-y: auto;">
      <form method="POST" action="./siteControllers/insert_sitedata.php" class="w-100">
      <input type="hidden" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
      <input type="hidden" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
        <div class="modal-header">
          <h5 class="modal-title card-title-text">Customize Package</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12 col-lg-7">
              <div class="border rounded p-3 h-100 modal-box">
                <h5 class="mb-2 card-title-text" id="gs_title">Package</h5>
                <div class="small modal-subtext mb-2">Select Features:</div>
                <ul id="gs_features" class="list-unstyled small mb-0 ps-1 p-text"></ul>
              </div>
            </div>

            <div class="col-12 col-lg-5">
              <div class="border rounded p-3 h-100 modal-box">
                <!-- Extra Features Section -->
                <div class="mb-3">
                  <span class="fw-bold modal-label">Extra Features</span>
                  <ul id="gs_extra" class="list-unstyled small mt-2"></ul>
                </div>

                <!-- Apply Coupon -->
                <div class="mb-3">
                  <label for="inp_coupon" class="form-label fw-bold modal-label">Apply Coupon</label>
                  <!-- <input type="text" class="form-control" name="apply_coupon" id="inp_coupon" placeholder="Enter coupon code"> -->
                   <input 
                    type="text" 
                    class="form-control" 
                    name="apply_coupon" 
                    id="inp_coupon" 
                    placeholder="Enter coupon code"
                    value="<?php echo isset($recievedCoupon) ? htmlspecialchars($recievedCoupon) : ''; ?>"
>

                </div>

                <!-- Total Price -->
                <div class="d-flex justify-content-between align-items-center fs-5">
                  <span class="fw-bold modal-label">Total Price</span>
                  <span class="fw-bold modal-total">₹<span id="gs_total">0.00</span></span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <input type="hidden" name="package_title" id="inp_package_title">
          <input type="hidden" name="total_price" id="inp_total_price">
          <input type="hidden" name="selected_features" id="inp_selected_features">
          <button type="submit" name="proceed-package" class="btn btn-success">Proceed</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function formatMoney(n){return (isNaN(n)?0:Number(n)).toFixed(2);}
let featuresData=[];

const gsModal=document.getElementById('getStartedModal');
gsModal.addEventListener('show.bs.modal',function(ev){
  const btn=ev.relatedTarget;
  const title=btn.getAttribute('data-title')||'Package';
  const featuresJson=btn.getAttribute('data-features')||'[]';

  document.getElementById('gs_title').textContent=title;
  document.getElementById('inp_package_title').value=title;

  // Parse features
  try{featuresData=JSON.parse(featuresJson);}catch(e){featuresData=[];}

  // Sort: checked first
  featuresData.sort((a,b)=>{
    if(a.status===b.status) return 0;
    return a.status==='checked' ? -1 : 1;
  });

  // Render with checkboxes
  const ul=document.getElementById('gs_features');
  ul.innerHTML='';
  featuresData.forEach((f,i)=>{
    const li=document.createElement('li');
    li.innerHTML=`
      <div class="form-check">
        <input type="checkbox" class="form-check-input feature_cb" id="feat_${i}"
          data-name="${f.feature}" data-price="${f.feature_price}"
          ${f.status==='checked'?'checked':''}>
        <label for="feat_${i}" class="form-check-label">
          ${f.feature} (₹${formatMoney(f.feature_price)})
        </label>
      </div>
    `;
    ul.appendChild(li);
  });

  recalcTotal();
});

function recalcTotal(){
  let total=0;
  let selected=[];
  let extras=[];
  document.querySelectorAll('.feature_cb').forEach(cb=>{
    const price=parseFloat(cb.getAttribute('data-price')||'0');
    const name=cb.getAttribute('data-name');
    const isChecked=cb.checked;
    const isDefault=cb.outerHTML.includes("checked");

    if(isChecked){
      total+=price;
      selected.push(name);
      // Extra means was initially unchecked but now selected
      if(cb.getAttribute('data-default')!=="true" && cb.defaultChecked===false){
        extras.push(`${name} (+₹${formatMoney(price)})`);
      }
    }
  });

  // Update UI
  document.getElementById('gs_total').textContent=formatMoney(total);
  document.getElementById('inp_total_price').value=total;
  document.getElementById('inp_selected_features').value=selected.join(', ');

  // Render extras
  const extraUl=document.getElementById('gs_extra');
  extraUl.innerHTML='';
  if(extras.length){
    extras.forEach(e=>{
      const li=document.createElement('li');
      li.textContent=e;
      extraUl.appendChild(li);
    });
  } else {
    extraUl.innerHTML='<li class="text-muted">No extra features</li>';
  }
}

document.addEventListener("DOMContentLoaded", function() {
  const couponField = document.getElementById("inp_coupon");
  if (couponField && couponField.value.trim() !== "") {
    // Highlight coupon input if prefilled
    couponField.classList.add("border-success");
  }
});


document.addEventListener('change',function(e){
  if(e.target.classList.contains('feature_cb')){
    recalcTotal();
  }
});
</script>


<?php require_once './includes/footer.php'; ?>
