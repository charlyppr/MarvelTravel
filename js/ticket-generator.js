// Fonction principale pour générer le billet PDF
function generateTicket(transactionId) {
  // Afficher un indicateur de chargement
  showLoadingIndicator();

  fetch(`../php/get-transaction.php?transaction=${transactionId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      // Créer un élément HTML temporaire pour le billet
      const ticketContainer = document.createElement("div");
      ticketContainer.id = "ticket-container";
      ticketContainer.style.width = "210mm";
      ticketContainer.style.height = "297mm";
      ticketContainer.style.position = "absolute";
      ticketContainer.style.left = "-9999px";
      ticketContainer.style.margin = "0";
      ticketContainer.style.padding = "0";
      ticketContainer.style.border = "none";
      ticketContainer.style.boxSizing = "border-box";
      document.body.appendChild(ticketContainer);

      // Générer le contenu HTML du billet avec un QR code pré-généré localement
      createTicketHTML(ticketContainer, data);

      // Ajouter un style explicite pour supprimer les marges sur le frame
      const ticketElements = ticketContainer.querySelectorAll(
        ".a, .ticket-wrapper, .ticket"
      );
      ticketElements.forEach((element) => {
        element.style.margin = "0";
        element.style.padding = "0";
        element.style.width = "100%";
        element.style.maxWidth = "100%";
      });

      html2canvas(ticketContainer, {
        scale: 4, // Réduction de la résolution de 5 à 2 pour des performances optimales
        useCORS: true,
        allowTaint: true,
        width: ticketContainer.offsetWidth,
        height: ticketContainer.offsetHeight,
        windowWidth: ticketContainer.offsetWidth,
        windowHeight: ticketContainer.offsetHeight,
        x: 0,
        y: 0,
        scrollX: 0,
        scrollY: 0,
        removeContainer: true,
        backgroundColor: null,
        imageTimeout: 0,
        letterRendering: true,
      })
        .then((canvas) => {
          // Utiliser une qualité d'image réduite mais suffisante (0.8 au lieu de 1.0)
          const imgData = canvas.toDataURL("image/jpeg", 0.85);

          // Optimisation des paramètres PDF
          const pdf = new jspdf.jsPDF({
            orientation: "portrait",
            unit: "mm",
            format: [210, 297], // Format A4 explicite
            compress: true,
            putOnlyUsedFonts: true,
            precision: 16, // Réduit de 30 à 16 pour de meilleures performances
          });

          // Définir les dimensions
          const pdfWidth = 210; // Taille A4 en mm
          const pdfHeight = 297; // Taille A4 en mm

          // Réinitialiser toute configuration de marge potentielle
          pdf.setDrawColor(0);
          pdf.setFillColor(255, 255, 255);
          pdf.setLineWidth(0);

          // Ajouter l'image au PDF, en utilisant une position précise
          pdf.addImage(
            imgData,
            "JPEG", // Utiliser JPEG au lieu de PNG pour réduire la taille
            -1,
            -1,
            pdfWidth,
            pdfHeight
          );

          // Fermer l'indicateur de chargement
          hideLoadingIndicator();

          pdf.save(`marvel-travel-ticket-${data.reservation_id}.pdf`);

          // Nettoyer après la génération
          document.body.removeChild(ticketContainer);
        })
        .catch((error) => {
          hideLoadingIndicator();
          console.error("Erreur lors de la génération du PDF:", error);
          alert(
            "Une erreur est survenue lors de la génération du PDF. Veuillez réessayer."
          );
        });
    })
    .catch((error) => {
      hideLoadingIndicator();
      console.error("Erreur lors de la génération du billet:", error);
      alert(
        "Une erreur est survenue lors de la génération du billet. Veuillez réessayer."
      );
    });
}

// Crée le contenu HTML du billet
function createTicketHTML(container, data) {
  // Préparer les données pour le QR code
  const qrData = {
    reservation_id: data.reservation_id,
    voyage: data.voyage,
    client: data.voyageurs[0].prenom + " " + data.voyageurs[0].nom,
    email: data.acheteur,
    date_debut: data.date_debut,
    date_fin: data.date_fin,
    nb_personne: data.voyageurs.length,
    montant: data.montant,
    transaction_id: data.transaction,
  };

  // URL optimisée pour le QR code avec taille réduite
  const qrCodeData = encodeURIComponent(JSON.stringify(qrData));
  const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${qrCodeData}`;

  const voyageurPrincipal = data.voyageurs[0];
  const duree = getDurationInDays(data.date_debut, data.date_fin);

  // Nouveau design moderne et premium pour le billet avec styles optimisés
  const html = `
    <style>
      /* Chargement inline de la police pour éviter les requêtes externes */
      @font-face {
        font-family: 'Poppins';
        font-style: normal;
        font-weight: 400;
        src: url('../police/Poppins-Regular.ttf');
      }
      
      #ticket-container * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
      }
      
      #ticket-container {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background-color: white;
      }
      
      #ticket-container .premium-ticket {
        width: 210mm;
        height: 297mm;
        position: relative;
        overflow: hidden;
        background: linear-gradient(to bottom, #ffffff, #f9f9f9);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      }
      
      #ticket-container .background-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0.3;
        z-index: 0;
      }
      
      #ticket-container .ticket-content {
        position: relative;
        z-index: 1;
        padding: 30px;
        height: 100%;
        display: flex;
        flex-direction: column;
      }
      
      /* Header Section */
      #ticket-container .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(175, 31, 36, 0.2);
        margin-bottom: 30px;
      }
      
      #ticket-container .logo-container {
        display: flex;
        align-items: center;
      }
      
      #ticket-container .marvel-logo {
        height: 40px;
        width: auto;
      }
      
      #ticket-container .ticket-title {
        margin-left: 15px;
        display: flex;
        flex-direction: column;
      }
      
      #ticket-container .ticket-title h1 {
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
      }
      
      #ticket-container .ticket-title span {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        color: #666;
        font-weight: 400;
      }
      
      #ticket-container .reservation-number {
        background: linear-gradient(135deg, #af1f24 0%, #e63946 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 10px rgba(175, 31, 36, 0.2);
      }
      
      /* Main Content */
      #ticket-container .ticket-main {
        display: flex;
        gap: 40px;
        margin-bottom: 30px;
        flex: 1;
        height: calc(100% - 150px);
      }
      
      #ticket-container .left-column {
        flex: 3;
      }
      
      #ticket-container .right-column {
        flex: 2;
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: relative;
        height: 100%;
      }
      
      /* Destination Image & Info */
      #ticket-container .destination-card {
        position: relative;
        height: 220px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      }
      
      #ticket-container .destination-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      #ticket-container .destination-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.4), transparent);
        padding: 30px 15px 15px;
        color: white;
      }
      
      #ticket-container .destination-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 10px;
      }
      
      #ticket-container .destination-details {
        display: flex;
        gap: 10px;
        font-size: 12px;
        flex-wrap: nowrap;
        justify-content: space-between;
        width: 100%;
      }
      
      #ticket-container .detail-item {
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
      }
      
      #ticket-container .detail-item svg {
        width: 14px;
        height: 14px;
        fill: white;
      }
      
      /* Information Cards */
      #ticket-container .info-card {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
      }
      
      #ticket-container .info-card h2 {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      
      #ticket-container .info-card h2 svg {
        width: 20px;
        height: 20px;
        fill: #af1f24;
      }
      
      #ticket-container .traveler-info {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }
      
      #ticket-container .info-item {
        margin-bottom: 5px;
      }
      
      #ticket-container .info-label {
        font-size: 12px;
        color: #666;
        margin-bottom: 3px;
      }
      
      #ticket-container .info-value {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
      }
      
      #ticket-container .passport-value {
        color: #af1f24;
        font-weight: 600;
      }
      
      /* QR Code Card */
      #ticket-container .qr-code-card {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
      }
      
      #ticket-container .qr-container {
        padding: 10px;
        background-color: white;
        border-radius: 12px;
        border: 1px dashed #af1f24;
        margin-bottom: 15px;
      }
      
      #ticket-container .qr-code {
        width: 100%;
        height: 100%;
      }
      
      #ticket-container .scan-text {
        font-size: 12px;
        color: #666;
        text-align: center;
        max-width: 180px;
      }
      
      /* Purchase Information */
      #ticket-container .purchase-info {
        display: flex;
        justify-content: space-between;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
        padding-top: 20px;
        margin-top: 20px;
      }
      
      #ticket-container .purchase-column {
        flex: 1;
      }
      
      #ticket-container .purchase-item {
        margin-bottom: 10px;
      }
      
      #ticket-container .price-value {
        font-size: 16px;
        font-weight: 600;
        color: #af1f24;
      }
      
      /* Eco Info & Footer */
      #ticket-container .eco-info {
        background: linear-gradient(135deg, #aee6c8 0%, #8ed7ad 100%);
        border-radius: 16px;
        padding: 20px;
        position: relative;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      }
      
      #ticket-container .eco-icon {
        width: 24px;
        height: 24px;
        fill: #2a7d4f;
        flex-shrink: 0;
        margin-top: 3px;
      }
      
      #ticket-container .eco-text {
        font-size: 12px;
        color: #2a5d42;
        line-height: 1.5;
      }
      
      #ticket-container .eco-text strong {
        font-weight: 600;
      }
      
      #ticket-container .ticket-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: auto;
        position: relative;
        height: 100%;
      }
      
      #ticket-container .footer-info {
        max-width: 60%;
      }
      
      #ticket-container .footer-title {
        font-size: 14px;
        font-weight: 600;
        color: #666;
        margin-bottom: 10px;
      }
      
      #ticket-container .footer-text {
        font-size: 11px;
        color: #666;
        margin-bottom: 10px;
        line-height: 1.4;
      }
      
      #ticket-container .stamp-container {
        position: absolute;
        bottom: 0;
        right: 10%;
        width: 120px;
        height: 120px;
        z-index: 10;
      }
      
      #ticket-container .stamp {
        width: 120px;
        height: 120px;
        object-fit: contain;
        position: absolute;
        bottom: 0;
        right: 0;
      }
      
      /* Bon à savoir section */
      #ticket-container .good-to-know {
        background-color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
      }
    </style>
    
    <div class="premium-ticket">
      <div class="background-pattern"></div>
      <div class="ticket-content">
        <!-- Header -->
        <header class="ticket-header">
          <div class="logo-container">
            <img class="marvel-logo" src="../img/svg/marvel-logo.svg" alt="Marvel Travel">
            <div class="ticket-title">
              <h1>E-Ticket</h1>
              <span>Votre voyage interdimensionnel</span>
            </div>
          </div>
          <div class="reservation-number">${data.reservation_id}</div>
        </header>
        
        <!-- Main Content -->
        <div class="ticket-main">
          <!-- Left Column -->
          <div class="left-column">
            <!-- Destination Card -->
            <div class="destination-card">
              <img class="destination-image" src="${getDestinationImage(
                data.voyage
              )}" alt="${data.voyage}">
              <div class="destination-overlay">
                <div class="destination-name">${data.voyage}</div>
                <div class="destination-details">
                  <div class="detail-item">
                    <svg viewBox="0 0 24 24"><path d="M7,11H9V13H7V11M11,11H13V13H11V11M15,11H17V13H15V11M19,4H18V2H16V4H8V2H6V4H5C3.89,4 3,4.9 3,6V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V6A2,2 0 0,0 19,4M19,20H5V9H19V20Z" /></svg>
                    ${formatDate(data.date_debut)} → ${formatDate(
    data.date_fin
  )}
                  </div>
                  <div class="detail-item">
                    <svg viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M17,13.8V14A1,1 0 0,1 16,15H12.5A1.5,1.5 0 0,1 11,13.5A1.5,1.5 0 0,1 12.5,12H15V11H12A1,1 0 0,1 11,10V9.2C11,9.08 11,9 11.12,8.88C11.24,8.76 11.32,8.76 11.44,8.76H15.56C15.68,8.76 15.76,8.76 15.88,8.88C16,9 16,9.08 16,9.2V10A1,1 0 0,1 15,11H12.5A1.5,1.5 0 0,1 11,9.5A1.5,1.5 0 0,1 12.5,8H15V7H11.12C11,7 10.92,7 10.8,6.88C10.68,6.76 10.68,6.68 10.68,6.56V5.56C10.68,5.44 10.68,5.36 10.8,5.24C10.92,5.12 11,5.12 11.12,5.12H15.88C16,5.12 16.08,5.12 16.2,5.24C16.32,5.36 16.32,5.44 16.32,5.56V6.56C16.32,6.68 16.32,6.76 16.2,6.88C16.08,7 16,7 15.88,7H13.38V8H16A1,1 0 0,1 17,9V9.8C17,9.92 17,10 16.88,10.12C16.76,10.24 16.68,10.24 16.56,10.24H12.12C12,10.24 11.92,10.24 11.8,10.12C11.68,10 11.68,9.92 11.68,9.8V9.68H11.62V10.12C11.62,10.24 11.62,10.32 11.74,10.44C11.86,10.56 11.94,10.56 12.06,10.56H16.31V12H13.69V13.56H15.88C16,13.56 16.08,13.56 16.2,13.68C16.32,13.8 16.32,13.88 16.32,14L16.31,13.8H17" /></svg>
                    ${duree} jours
                  </div>
                  <div class="detail-item">
                    <svg viewBox="0 0 24 24"><path d="M16 17V19H2V17S2 13 9 13 16 17 16 17M12.5 7.5A3.5 3.5 0 1 0 9 11A3.5 3.5 0 0 0 12.5 7.5M15.94 13A5.32 5.32 0 0 1 18 17V19H22V17S22 13.37 15.94 13M15 4A3.39 3.39 0 0 0 13.07 4.59A5 5 0 0 1 13.07 10.41A3.39 3.39 0 0 0 15 11A3.5 3.5 0 0 0 15 4Z" /></svg>
                    ${data.voyageurs.length} voyageur${
    data.voyageurs.length > 1 ? "s" : ""
  }
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Traveler Info Card -->
            <div class="info-card">
              <h2>
                <svg viewBox="0 0 24 24"><path d="M12,4A4,4 0 0,1 16,8A4,4 0 0,1 12,12A4,4 0 0,1 8,8A4,4 0 0,1 12,4M12,14C16.42,14 20,15.79 20,18V20H4V18C4,15.79 7.58,14 12,14Z" /></svg>
                Informations Voyageur
              </h2>
              <div class="traveler-info">
                <div class="info-item">
                  <div class="info-label">Nom</div>
                  <div class="info-value">${voyageurPrincipal.nom}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Prénom</div>
                  <div class="info-value">${voyageurPrincipal.prenom}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Nationalité</div>
                  <div class="info-value">${voyageurPrincipal.nationalite}</div>
                </div>
                <div class="info-item">
                  <div class="info-label">Date de naissance</div>
                  <div class="info-value">${formatDate(
                    voyageurPrincipal.date_naissance
                  )}</div>
                </div>
                <div class="info-item" style="grid-column: span 2;">
                  <div class="info-label">N° de passeport</div>
                  <div class="info-value passport-value">${formatPassport(
                    voyageurPrincipal.passport
                  )}</div>
                </div>
              </div>
              
              <!-- Purchase Info -->
              <div class="purchase-info">
                <div class="purchase-column">
                  <div class="purchase-item">
                    <div class="info-label">Date d'achat</div>
                    <div class="info-value">${formatDate(data.date_achat)}</div>
                  </div>
                  <div class="purchase-item">
                    <div class="info-label">Acheté sur</div>
                    <div class="info-value">Marvel Travel</div>
                  </div>
                </div>
                <div class="purchase-column">
                  <div class="purchase-item">
                    <div class="info-label">Méthode</div>
                    <div class="info-value">Carte bancaire</div>
                  </div>
                  <div class="purchase-item">
                    <div class="info-label">Prix</div>
                    <div class="info-value price-value">${formatPrice(
                      data.montant
                    )} €</div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Eco Info -->
            <div class="eco-info">
              <svg class="eco-icon" viewBox="0 0 24 24"><path d="M17,8C8,10 5.9,16.17 3.82,21.34L5.71,22L6.66,19.7C7.14,19.87 7.64,20 8,20C19,20 22,3 22,3C21,5 14,5.25 9,6.25C4,7.25 2,11.5 2,13.5C2,15.5 3.75,17.25 3.75,17.25C7,8 17,8 17,8Z" /></svg>
              <div class="eco-text">
                En choisissant nos voyages, vous contribuez à une action <strong>éco-responsable</strong> grâce à <strong>Infinity Green</strong>, notre programme dédié à l'environnement. Vous participez activement à la réduction de l'impact écologique de vos trajets interdimensionnels.
              </div>
            </div>
          </div>
          
          <!-- Right Column -->
          <div class="right-column">
            <!-- QR Code Card -->
            <div class="qr-code-card">
              <div class="qr-container">
                <img class="qr-code" src="${qrCodeUrl}" alt="QR Code">
              </div>
              <div class="scan-text">
                Utilisez ce QR code lors de l'embarquement
              </div>
            </div>
            
            <!-- Bon à savoir section -->
            <div class="good-to-know">
              <div class="footer-title">Bon à savoir</div>
              <p class="footer-text">
                <strong>Toutes les informations</strong> relatives à votre voyage, y compris les détails sur les transports, le lieu de prise en charge et autres informations importantes, vous seront envoyées par e-mail.
              </p>
              <p class="footer-text">
                Votre billet est nominatif, personnel, incessible, valable uniquement pour le trajet, la date et la destination désignée.
              </p>
              <p class="footer-text">
                Si votre voyage inclut plusieurs personnes, elles sont toutes incluses sur votre billet.
              </p>
            </div>
            
            <!-- Stamp at bottom right of the entire page -->
            <div class="stamp-container">
              <img class="stamp" src="../img/tampon.png" alt="Tampon officiel">
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  container.innerHTML = html;
}

// Afficher un indicateur de chargement
function showLoadingIndicator() {
  // Créer un élément pour l'indicateur de chargement
  const loadingIndicator = document.createElement("div");
  loadingIndicator.id = "ticket-loading-indicator";
  loadingIndicator.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  `;

  loadingIndicator.innerHTML = `
    <div style="text-align: center;">
      <div style="border: 4px solid #f3f3f3; border-top: 4px solid #af1f24; border-radius: 50%; width: 40px; height: 40px; margin: 0 auto; animation: spin 2s linear infinite;"></div>
      <p style="margin-top: 10px; font-family: Arial, sans-serif; color: #333;">Génération de votre billet...</p>
    </div>
    <style>
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
  `;

  document.body.appendChild(loadingIndicator);
}

// Cacher l'indicateur de chargement
function hideLoadingIndicator() {
  const loadingIndicator = document.getElementById("ticket-loading-indicator");
  if (loadingIndicator) {
    document.body.removeChild(loadingIndicator);
  }
}

// Calcule la durée en jours entre deux dates
function getDurationInDays(startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

// Obtient une image correspondant à la destination
function getDestinationImage(destination) {
  const destinations = {
    "Asgard": "../img/destinations/asgard.jpg",
    "Atlantis": "../img/destinations/atlantis.jpg",
    "The Battleworld": "../img/destinations/battleworld.jpg",
    "Genosha": "../img/destinations/genosha.jpg",
    "Hala": "../img/destinations/hala.jpg",
    "Kree Homeworld": "../img/destinations/kree-homeworld.jpg",
    "Morag": "../img/destinations/morag.jpg",
    "New York": "../img/destinations/new-york.jpg",
    "Sakaar": "../img/destinations/sakaar.jpg",
    "The Sanctum Sanctorum": "../img/destinations/sanctum-sanctorum.jpg",
    "The Negative Zone": "../img/destinations/the-negative-zone.jpg",
    "The Savage Land": "../img/destinations/the-savage-land.jpg",
    "Titan": "../img/destinations/titan.jpg",
    "Wakanda": "../img/destinations/wakanda.jpg",
    "Xandar": "../img/destinations/xandar.jpg",
  };

  return destinations[destination];
}

// Format le numéro de passeport avec espaces
function formatPassport(passport) {
  if (!passport) return "";

  // Ajoute des espaces tous les 3 caractères
  return passport
    .toString()
    .replace(/(.{3})/g, "$1 ")
    .trim();
}

// Formatage de la date
function formatDate(dateString) {
  if (!dateString) return "";

  if (dateString.includes(" ")) {
    const [datePart, timePart] = dateString.split(" ");
    const formattedDate = formatDate(datePart);
    return `${formattedDate} ${timePart.substring(0, 5)}`;
  }

  const parts = dateString.split("-");
  if (parts.length !== 3) return dateString;
  return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

// Formatage du prix
function formatPrice(price) {
  return parseFloat(price).toLocaleString("fr-FR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

// Initialisation: ajout des gestionnaires d'événements
document.addEventListener("DOMContentLoaded", () => {
  // Préchargement des bibliothèques nécessaires pour accélérer la génération
  Promise.all([
    loadScript(
      "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
    ),
    loadScript(
      "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"
    ),
  ])
    .then(() => {
      console.log("Bibliothèques PDF préchargées avec succès");
    })
    .catch((error) => {
      console.warn("Erreur lors du préchargement des bibliothèques:", error);
    });

  const ticketButtons = document.querySelectorAll(".generate-ticket-btn");
  ticketButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      // Empêcher la propagation de l'événement et le comportement par défaut
      e.preventDefault();
      e.stopPropagation();

      const transactionId = e.currentTarget.dataset.transaction;
      if (transactionId) {
        // Vérifier si les bibliothèques sont déjà chargées
        if (window.html2canvas && window.jspdf) {
          generateTicket(transactionId);
        } else {
          // Si les bibliothèques ne sont pas encore chargées, les charger puis générer
          Promise.all([
            loadScript(
              "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
            ),
            loadScript(
              "https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"
            ),
          ]).then(() => {
            generateTicket(transactionId);
          });
        }
      }
    });
  });
});

// Charge un script externe de manière asynchrone
function loadScript(url) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${url}"]`)) {
      resolve();
      return;
    }

    const script = document.createElement("script");
    script.src = url;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
}
