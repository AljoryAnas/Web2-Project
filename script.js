
function makeEl(tag, className) {
  const el = document.createElement(tag);
  if (className) el.className = className;
  return el;
}

const ingredientsContainer = document.getElementById("ingredientsContainer");
const addIngredientBtn = document.getElementById("addIngredientBtn");

function wireIngredientRemoveButtons() {
  if (!ingredientsContainer) return;

  const buttons = ingredientsContainer.querySelectorAll(".remove-ingredient");
  buttons.forEach(btn => {
    btn.onclick = () => {
      const row = btn.closest(".ingredient-row");
      if (row) row.remove();
      enforceAtLeastOneIngredient();
    };
  });
}

function enforceAtLeastOneIngredient() {
  if (!ingredientsContainer) return;

  const rows = ingredientsContainer.querySelectorAll(".ingredient-row");
  rows.forEach((row, idx) => {
    const btn = row.querySelector(".remove-ingredient");
    if (!btn) return;
    btn.disabled = (rows.length === 1 && idx === 0);
  });
}

if (addIngredientBtn) {
  addIngredientBtn.onclick = () => {
    const row = makeEl("div", "row ingredient-row");
    row.innerHTML = `
      <div class="col">
        <label>Ingredient Name</label><br>
        <input type="text" name="ingredientName[]" required>
      </div>
      <div class="col">
        <label>Quantity</label><br>
        <input type="text" name="ingredientQty[]" required>
      </div>
      <div class="col small">
        <label>&nbsp;</label><br>
        <button type="button" class="btn-secondary remove-ingredient">Remove</button>
      </div>
    `;
    ingredientsContainer.appendChild(row);
    wireIngredientRemoveButtons();
    enforceAtLeastOneIngredient();
  };
}

const stepsContainer = document.getElementById("stepsContainer");
const addStepBtn = document.getElementById("addStepBtn");

function updateStepLabels() {
  if (!stepsContainer) return;

  const rows = stepsContainer.querySelectorAll(".step-row");
  rows.forEach((row, i) => {
    row.querySelector("label").textContent = "Step " + (i + 1);
  });
}

function wireStepRemoveButtons() {
  if (!stepsContainer) return;

  const buttons = stepsContainer.querySelectorAll(".remove-step");
  buttons.forEach(btn => {
    btn.onclick = () => {
      const row = btn.closest(".step-row");
      if (row) row.remove();
      updateStepLabels();
      enforceAtLeastOneStep();
    };
  });
}

function enforceAtLeastOneStep() {
  if (!stepsContainer) return;

  const rows = stepsContainer.querySelectorAll(".step-row");
  rows.forEach((row, idx) => {
    const btn = row.querySelector(".remove-step");
    if (!btn) return;
    btn.disabled = (rows.length === 1 && idx === 0);
  });
}

if (addStepBtn) {
  addStepBtn.onclick = () => {
    const row = makeEl("div", "row step-row");
    row.innerHTML = `
      <div class="col wide">
        <label>Step ${stepsContainer.children.length + 1}</label><br>
        <input type="text" name="stepText[]" required>
      </div>
      <div class="col small">
        <label>&nbsp;</label><br>
        <button type="button" class="btn-secondary remove-step">Remove</button>
      </div>
    `;
    stepsContainer.appendChild(row);
    wireStepRemoveButtons();
    updateStepLabels();
    enforceAtLeastOneStep();
  };
}

wireIngredientRemoveButtons();
wireStepRemoveButtons();
enforceAtLeastOneIngredient();
enforceAtLeastOneStep();
updateStepLabels();
