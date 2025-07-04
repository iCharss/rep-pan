// Cart state
let cart = [];

// DOM elements
const cartItemsContainer = document.querySelector(".cart-items-container");
const cartItemsEl = document.querySelector(".cart-items");
const emptyCartEl = document.querySelector(".empty-cart");
const searchInput = document.querySelector(".search-input");
const filterChips = document.querySelectorAll(".filter-chip");
const cartCountEl = document.getElementById("cart-count");
const menuContainer = document.querySelector(".menu-items");
const notificationEl = document.createElement('div');
notificationEl.className = 'cart-notification';
document.body.appendChild(notificationEl);

// Añade esto al inicio de tu archivo JS (junto con las otras constantes)
function formatPrice(price) {
  // Asegurarse que es un número
  const number = typeof price === 'string' ? parseFloat(price) : price;
  
  return number.toLocaleString('es-AR', {
    style: 'decimal',
    minimumFractionDigits: 0,  // Cambiado de 2 a 0
    maximumFractionDigits: 0   // Cambiado de 2 a 0
  });
}

// Función para cargar productos (repuestos o maquinaria)
async function loadMenuItems(endpoint, containerId) {
  try {
    const response = await fetch(endpoint);
    const productos = await response.json();

    const container = document.getElementById(containerId);
    
    container.innerHTML = productos.map((item) => {
      // Si el producto tiene capacidades (amasadoras)
      if (item.capacidades && item.tipo_variante === 'capacidad') {
        // Parsear las capacidades si es un string
        const capacidades = typeof item.capacidades === 'string' 
          ? JSON.parse(item.capacidades)
          : item.capacidades;
        
        // Verificar que capacidades es un array antes de mapear
        if (!Array.isArray(capacidades)) {
          console.error('Capacidades no es un array:', capacidades);
          return '';
        }
        
        let selectHtml = '';
        let precioMinimo = 0;
        
        if (capacidades.length > 0) {
          const capacidadesOptions = capacidades.map(capa => 
            `<option value="${capa.valor}" data-precio="${capa.precio}" data-stock="${capa.stock}">
              ${capa.valor} - $${formatPrice(capa.precio)}
            </option>`
          ).join('');
        
          const precios = capacidades.map(c => c.precio);
          precioMinimo = precios.length > 0 ? Math.min(...precios) : 0;
        
          selectHtml = `
            <select class="capacity-select" data-id="${item.id}">
              <option value="">Seleccione capacidad (Obligatorio)</option>
              ${capacidadesOptions}
            </select>
          `;
        } else {
          // No hay capacidades disponibles
          selectHtml = `
            <select class="capacity-select" data-id="${item.id}" disabled>
              <option>No hay stock.</option>
            </select>
          `;
        }
        
        return `
          <div class="menu-item" data-id="${item.id}" data-category="${item.categoria}" data-precio-minimo="${precioMinimo}">
            <div class="item-details">
              <div class="item-header">
                <h3 class="item-name">${item.nombre}</h3>
                <span class="item-price">Desde $${formatPrice(precioMinimo)}</span>
              </div>
              <div class="item-images">
                ${item.imagen.map(img => `
                  <img src="public/images/${img}" alt="${item.nombre}" class="item-image">
                `).join('')}
              </div>
              <div class="variant-selector">
                ${selectHtml}
              </div>
                <button class="add-to-cart with-variant" data-id="${item.id}" data-category="${item.categoria}" disabled>
                  <span class="add-text"><i class="fas fa-cart-plus"></i> Agregar al carrito</span>
                  <span class="out-of-stock-text" style="display:none; color:red;"><i class="fas fa-cart-plus"></i> Producto agotado</span>
                </button>
            </div>
          </div>
        `;
      }
      
      // Producto normal (sin variantes)
      return `
        <div class="menu-item" data-id="${item.id}" data-category="${item.categoria}">
          <div class="item-details">
            <div class="item-header">
              <h3 class="item-name">${item.nombre}</h3>
              <span class="item-price">$${formatPrice(item.precio)}</span>
            </div>
            <div class="item-images">
                ${item.imagen.map(img => `
                  <img src="public/images/${img}" alt="${item.nombre}" class="item-image">
                `).join('')}
              </div>
            <br>
            <p class="item-description">${item.descripcion}</p>
            ${item.stock > 0 
              ? `<button class="add-to-cart" data-id="${item.id}" data-category="${item.categoria}">
                  <i class="fas fa-cart-plus"></i> Agregar al carrito
                </button>` 
              : '<p class="out-of-stock">Producto agotado</p>'}
          </div>
        </div>
      `;
    }).join("");

    // Manejar selección de capacidad
    document.querySelectorAll(".capacity-select").forEach(select => {
      select.addEventListener("change", function() {
        const itemId = this.dataset.id;
        const menuItem = this.closest(".menu-item");
        const addButton = menuItem.querySelector(".add-to-cart");
        const itemDetails = this.closest(".item-details");
        const precioMinimo = menuItem.dataset.precioMinimo;
        
        if (this.value) {
          const selectedOption = this.options[this.selectedIndex];
          const precio = selectedOption.dataset.precio;
          const stock = parseInt(selectedOption.dataset.stock);
          
          // Actualizar precio mostrado
          itemDetails.querySelector(".item-price").textContent = `$${formatPrice(precio)}`;
          
          // Verificar stock y actualizar botón
          if (stock > 0) {
          addButton.querySelector('.add-text').style.display = 'inline';
          addButton.querySelector('.out-of-stock-text').style.display = 'none';
          addButton.disabled = false;
        } else {
          addButton.querySelector('.add-text').style.display = 'none';
          addButton.querySelector('.out-of-stock-text').style.display = 'inline';
          addButton.disabled = true;
        }
        } else {
      // Restaurar estado original cuando no hay selección
      addButton.querySelector('.add-text').style.display = 'inline';
      addButton.querySelector('.out-of-stock-text').style.display = 'none';
      addButton.disabled = true;
      itemDetails.querySelector(".item-price").textContent = `Desde $${formatPrice(precioMinimo)}`; // Restaurar precio inicial
    }
      });
    });

    // Agregar eventos a los botones "Agregar al carrito"
    document.querySelectorAll(".add-to-cart").forEach(button => {
      button.addEventListener("click", (e) => {
        const menuItem = e.target.closest(".menu-item");
        const itemId = parseInt(menuItem.dataset.id);
        const category = menuItem.dataset.category;
        
        // Para productos con variante
        if (button.classList.contains("with-variant")) {
          const select = menuItem.querySelector(".capacity-select");
          const selectedOption = select.options[select.selectedIndex];
          
          if (!select.value) {
            showNotification("Por favor seleccione una capacidad", "error");
            return;
          }
          
          const variantData = {
            capacidad: select.value,
            precio: parseFloat(selectedOption.dataset.precio),
            stock: parseInt(selectedOption.dataset.stock)
          };
          
          addToCart(itemId, productos, category, variantData);
        } else {
          // Producto normal
          addToCart(itemId, productos, category);
        }
      });
    });
    
    setupImageNavigation(); // Inicializa la navegación de imágenes
    
  } catch (error) {
    console.error("Error al cargar los productos:", error);
  }
}

// Inicializar la página
document.addEventListener("DOMContentLoaded", () => {
  const path = window.location.pathname;

  if (path === "/repuestos") {
    loadMenuItems("../public/obtener_productos.php", "productos-container"); 
  } else if (path === "/maquinaria") {
    loadMenuItems("../public/obtener_maquinaria.php", "maquinaria-container"); 
  }

  // Verificar existencia de elementos antes de agregar event listeners
    if (searchInput) {
      searchInput.addEventListener("input", searchItems);
    }

  filterChips.forEach((chip) => {
    chip.addEventListener("click", () => {
      filterChips.forEach((c) => c.classList.remove("active"));
      chip.classList.add("active");
      filterItems(chip.textContent.trim());
    });
  });
});

// Filtrar
function filterItems(category) {
  document.querySelectorAll(".menu-item").forEach((item) => {
    const itemCategory = item.dataset.category;

    // Si la categoría es "Todas las Maquinas" o "Todos los Repuestos", mostramos todos los productos
    if (category === "Todas las maquinas" || category === "Todos los Repuestos" || category === "Todos") {
      item.style.display = "block";
    } else {
      item.style.display = itemCategory === category ? "block" : "none";
    }
  });
}

// Función para mostrar notificaciones
let notificationTimeout;

function showNotification(message, type = 'success') {
  // Cancelar el timeout anterior si existe
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  // Aplicar clase para animación de salida rápida
  notificationEl.style.animation = 'fadeOutUp 0.2s ease-out';
  
  // Esperar a que termine la animación de salida antes de mostrar la nueva
  setTimeout(() => {
    notificationEl.textContent = message;
    notificationEl.className = `cart-notification ${type}`;
    notificationEl.style.display = 'block';
    notificationEl.style.animation = 'fadeInDown 0.3s ease-out';
    
    // Configurar nuevo timeout
    notificationTimeout = setTimeout(() => {
      notificationEl.style.animation = 'fadeOutUp 0.3s ease-out';
      setTimeout(() => {
        notificationEl.style.display = 'none';
      }, 300);
    }, 2000); // Reducido a 2 segundos para mejor experiencia
  }, 200);
}

// Búsqueda de repuestos
function searchItems() {
  const searchTerm = searchInput.value.toLowerCase().trim();
  document.querySelectorAll(".menu-item").forEach((item) => {
    const name = item.querySelector(".item-name").textContent.toLowerCase();
    const description = item.querySelector(".item-description").textContent.toLowerCase();
    item.style.display = name.includes(searchTerm) || description.includes(searchTerm) ? "block" : "none";
  });
}

// Agregar un repuesto al carrito
async function addToCart(itemId, productos, category, variantData = null) {
  const item = productos.find(item => item.id === itemId);
  if (!item) return;

  // Crear objeto para el carrito
  let cartItem = {
    ...item,
    id: itemId,
    quantity: 1,
    category: category,
    // Para productos con variante, sobreescribimos precio y stock
    ...(variantData && {
      precio: variantData.precio,
      stock: variantData.stock,
      variante: {
        tipo: item.tipo_variante,
        valor: variantData.capacidad
      }
    })
  };

  // Verificar stock (para productos con variante)
  if (variantData) {
    if (variantData.stock <= 0) {
      showNotification(`No hay stock disponible para la capacidad ${variantData.capacidad}`, 'error');
      return;
    }
  } else {
    // Verificación de stock normal (tu código existente)
    const stockResponse = await fetch("../public/verif_stock.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id: itemId, category: category }),
    });

    const stockData = await stockResponse.json();
    if (!stockData.success) {
      showNotification(stockData.message, 'error');
      return;
    }
    cartItem.stock = stockData.stock;
  }

  // Buscar si ya está en el carrito (comparando también la variante si existe)
  const existingItemIndex = cart.findIndex(cartItem => 
    cartItem.id === itemId && 
    (!cartItem.variante || cartItem.variante.valor === variantData?.capacidad)
  );

  if (existingItemIndex !== -1) {
    const existingItem = cart[existingItemIndex];
    if (existingItem.quantity < existingItem.stock) {
      existingItem.quantity++;
      showNotification(`Aumentaste la cantidad de ${existingItem.nombre} ${existingItem.variante?.valor || ''}`);
    } else {
      showNotification(`No hay suficiente stock para ${existingItem.nombre} ${existingItem.variante?.valor || ''}`, 'warning');
      return;
    }
  } else {
    cart.push(cartItem);
    showNotification(`Agregaste ${item.nombre} ${variantData?.capacidad || ''} al carrito.`);
  }

  updateCart();
}

// Eliminar un repuesto del carrito (reducir cantidad)
function removeFromCart(itemId, variantValue = null) {
  console.log(`Intentando eliminar producto con id: ${itemId} y variante: ${variantValue || 'ninguna'}`); 
  
  // Buscar el ítem considerando la variante si existe
  const itemIndex = cart.findIndex(item => 
    item.id === itemId && 
    (variantValue !== null ? (item.variante?.valor === variantValue) : true)
  );
  
  if (itemIndex === -1) {
    console.log('Producto no encontrado en el carrito');
    return;
  }
  
  const item = cart[itemIndex];
  const itemName = item.nombre;
  const variantInfo = item.variante?.valor ? ` (${item.variante.valor})` : '';
  
  // Mostrar notificación y actualizar cantidad
  if (item.quantity > 1) {
    item.quantity--;
    showNotification(`Redujiste la cantidad de ${itemName}${variantInfo}`, 'warning');
  } else {
    cart.splice(itemIndex, 1);
    showNotification(`Eliminaste ${itemName}${variantInfo} del carrito`, 'warning');
  }
  
  updateCart();
}

// Actualizar el carrito
function updateCart() {
  console.log("Actualizando carrito...");
  console.log("Estado del carrito:", cart);
  
  const itemCount = cart.reduce((total, item) => total + item.quantity, 0);
  cartCountEl.textContent = itemCount;

  // Mostrar u ocultar elementos según el estado del carrito
  if (itemCount === 0) {
    cartCountEl.style.display = "none";
    emptyCartEl.style.display = "block";
    cartItemsEl.style.display = "none";
    cartItemsContainer.classList.remove("cart-has-items");
  } else {
    cartCountEl.style.display = "block";
    emptyCartEl.style.display = "none";
    cartItemsEl.style.display = "flex";
    cartItemsContainer.classList.add("cart-has-items");
  }

  // Generar HTML para los items del carrito
  cartItemsEl.innerHTML = cart.map(item => `
    <div class="cart-item" 
      data-id="${item.id}"
      data-category="${item.categoria}" 
      ${item.variante ? `data-variant="${item.variante.valor}"` : ''}>
      <img src="../public/images/${item.imagen[0]}" alt="${item.nombre}" class="cart-item-image">
      <div class="cart-item-details">
        <button class="remove-item" data-id="${item.id}">❌</button>
        <div class="cart-item-name">
          ${item.nombre}
          ${item.variante ? `<span class="variant-info">(${item.variante.valor})</span>` : ''}
        </div>
        <div class="cart-item-actions">
          <div class="quantity-control">
            <button class="quantity-btn decrease" data-id="${item.id}">-</button>
            <span class="quantity">${item.quantity}</span>
            <button class="quantity-btn increase" data-id="${item.id}" 
              ${item.quantity >= item.stock ? "disabled" : ""}>+</button>
          </div>
          <span class="cart-item-price">$${formatPrice(item.precio * item.quantity)}</span>
        </div>
      </div>
    </div>
  `).join("");

  // Asignar eventos a los botones
  // En updateCart(), modifica el event listener del botón decrease:
    document.querySelectorAll(".decrease").forEach(button => {
      button.addEventListener("click", (e) => {
        const cartItemEl = e.target.closest(".cart-item");
        const itemId = parseInt(cartItemEl.dataset.id);
        
        // Obtener la variante de dos formas posibles (elige una):
        // Opción 1: Del texto entre paréntesis
        // const variantValue = cartItemEl.querySelector(".variant-info")?.textContent?.match(/\(([^)]+)\)/)?.[1];
        
        // Opción 2: Del data-attribute (más recomendado)
        const variantValue = cartItemEl.dataset.variant || null;
        
        removeFromCart(itemId, variantValue);
      });
    });

  document.querySelectorAll(".increase").forEach(button => {
      button.addEventListener("click", async (e) => {
        const cartItemEl = e.target.closest(".cart-item");
        const itemId = parseInt(cartItemEl.dataset.id);
        const variantValue = cartItemEl.querySelector(".variant-info")?.textContent?.replace(/[()]/g, '');
        
        // Buscar el ítem específico considerando la variante
        const cartItem = cart.find(item => 
          item.id === itemId && 
          (variantValue ? (item.variante?.valor === variantValue) : true)
        );
        
        if (cartItem) {
          // Verificar stock antes de aumentar
          if (cartItem.quantity >= cartItem.stock) {
            showNotification(`No hay suficiente stock para ${cartItem.nombre} ${variantValue ? `(${variantValue})` : ''}`, 'warning');
            return;
          }
          
          // Para productos con variante
          if (cartItem.variante) {
            const variantData = {
              capacidad: cartItem.variante.valor,
              precio: cartItem.precio,
              stock: cartItem.stock
            };
            await addToCart(itemId, cart, cartItem.category, variantData);
          } else {
            // Producto normal
            await addToCart(itemId, cart, cartItem.category);
          }
        }
      });
    });

  document.querySelectorAll(".remove-item").forEach(button => {
    button.addEventListener("click", (e) => {
        const cartItemEl = e.target.closest(".cart-item");
        const itemId = parseInt(cartItemEl.dataset.id);
        const variantValue = cartItemEl.querySelector(".variant-info")?.textContent?.replace(/[()]/g, '');
        removeItemCompletely(itemId, variantValue);
      });
    });

  updateTotals();
}


// Calcular y mostrar los totales
function updateTotals() {
  const subtotal = cart.reduce((total, item) => total + parseFloat(item.precio) * item.quantity, 0);
  const total = subtotal;
  document.getElementById("total").textContent = `$${formatPrice(total)}`;
}

// Eliminar un producto completamente del carrito
function removeItemCompletely(itemId, variantValue = null) {
  console.log(`Eliminando completamente producto con id: ${itemId} y variante: ${variantValue}`);
  
  const removedItems = cart.filter(item => 
    item.id === itemId && 
    (variantValue ? (item.variante?.valor === variantValue) : true)
  );
  
  cart = cart.filter(item => 
    !(item.id === itemId && 
    (variantValue ? (item.variante?.valor === variantValue) : true))
  );
  
  if (removedItems.length > 0) {
    const itemName = removedItems[0].nombre;
    const variantInfo = removedItems[0]?.variante?.valor ? ` (${removedItems[0].variante.valor})` : '';
    showNotification(`Eliminaste ${itemName}${variantInfo} del carrito`, 'warning');
  }
  
  updateCart();
}


// Elementos
const cartToggleBtn = document.getElementById("cart-toggle");
const cartCloseBtn = document.getElementById("cart-close");
const cartSection = document.getElementById("cart-section");

if (cartToggleBtn && cartSection) {
  cartToggleBtn.addEventListener("click", () => {
    cartSection.style.display = "block";
  });
}


const checkoutForm = document.getElementById("checkout-form");
if (checkoutForm) {
  checkoutForm.addEventListener("submit", function(event) {
    if (cart && cart.length > 0) {
      document.getElementById("cart-data").value = JSON.stringify(cart);
    } else {
      showNotification('El carrito está vacío.', 'error');
      event.preventDefault();
    }
  });
}

const paymentForm = document.getElementById('payment-form');
if (paymentForm) {
  paymentForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
      const response = await fetch('/procesar_pago', {
        method: 'POST',
        body: new FormData(this)
      });
      
      const data = await response.json();
      
      if (response.ok && data.redirectUrl) {
        window.location.href = data.redirectUrl;
      } else {
        alert(data.error || 'Error al procesar el pago');
      }
    } catch (error) {
      alert('Error de conexión con el servidor');
    }
  });
}

// Función para manejar la navegación de imágenes
function setupImageNavigation() {
  document.querySelectorAll('.item-images').forEach(imagesContainer => {
    const images = imagesContainer.querySelectorAll('.item-image');
    if (images.length <= 1) return;
    
    // Crear indicadores
    const indicators = document.createElement('div');
    indicators.className = 'image-indicators';
    
    images.forEach((_, index) => {
      const indicator = document.createElement('div');
      indicator.className = `image-indicator ${index === 0 ? 'active' : ''}`;
      indicator.dataset.index = index;
      indicators.appendChild(indicator);
    });
    
    imagesContainer.parentNode.insertBefore(indicators, imagesContainer.nextSibling);
    
    // Eventos para los indicadores
    indicators.querySelectorAll('.image-indicator').forEach(indicator => {
      indicator.addEventListener('click', () => {
        const index = parseInt(indicator.dataset.index);
        imagesContainer.scrollTo({
          left: images[index].offsetLeft - imagesContainer.offsetLeft,
          behavior: 'smooth'
        });
        
        // Actualizar indicadores activos
        indicators.querySelectorAll('.image-indicator').forEach(ind => 
          ind.classList.remove('active'));
        indicator.classList.add('active');
      });
    });
    
    // Actualizar indicadores al hacer scroll
    imagesContainer.addEventListener('scroll', () => {
      const scrollPosition = imagesContainer.scrollLeft + imagesContainer.offsetWidth / 2;
      
      images.forEach((img, index) => {
        const imgStart = img.offsetLeft - imagesContainer.offsetLeft;
        const imgEnd = imgStart + img.offsetWidth;
        
        if (scrollPosition >= imgStart && scrollPosition <= imgEnd) {
          indicators.querySelectorAll('.image-indicator').forEach(ind => 
            ind.classList.remove('active'));
          indicators.querySelector(`.image-indicator[data-index="${index}"]`).classList.add('active');
        }
      });
    });
  });
}